<?php
/**
 * Created by PhpStorm.
 * User: toby
 * Date: 21/02/15
 * Time: 18:27
 */

namespace CubicMushroom\Slim\Middleware;

use CubicMushroom\OptionsTrait\OptionTrait;
use CubicMushroom\Slim\Middleware\DoctrineORMMiddleware\Exception\InvalidOptionValueException;
use CubicMushroom\Slim\Middleware\DoctrineORMMiddleware\Exception\MissingSettingException;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use Doctrine\ORM\Tools\Setup;
use Slim\Middleware;

/**
 * Class DoctrineORMMiddleware
 *
 * Middleware that adds a Doctrine ORM Entity Manager to $app->container
 *
 * Config options are documented in the __constructor() documentation
 *
 * @package CubicMushroom\Slim\Middleware
 */
class DoctrineORMMiddleware extends Middleware
{

    use OptionTrait;

    const OPTION_SETTINGS_KEY = 'settingsKey';

    const OPTION_SERVICE_NAME = 'serviceName';

    const OPTION_ANNOTATION_PATHS = 'annotationMetadataPaths';

    const OPTION_ANNOTATIONS_AUTOLOADERS = 'annotationsAutoloaders';

    const OPTION_ANNOTATIONS_FILES = 'annotationsFiles';

    const OPTION_ANNOTATIONS_NAMESPACES = 'annotationNamespaces';

    /**
     * Key used for the doctrine settings within the Slim app settings, if another is not provided the passed options
     */
    const DEFAULT_SETTINGS_KEY = 'doctrine';

    const DEFAULT_SERVICE_NAME = '@entity_manager';


    /**
     * Sets up the Middleware class with provided options
     *
     * Options...
     * - 'settingsKey'             - [optional] String indicating where to find the doctrine settings in the app config
     *                               settings.  If not provided, will use the DEFAULT_SETTINGS_KEY value instead.
     * - 'serviceName'             - [optional] String to use when registering the entity manager service.  If not
     *                               provided will use DEFAULT_SERVICE_NAME value.
     * - 'annotationMetadataPaths' - [required] Array of paths to read annotations from if wanting to use the
     *                               annotation parser
     * - 'annotationsAutoloaders'   - [optional] Array of callables to register to autoload annotation classes
     * - 'annotationsFiles'         - [optional] Array of strings containing files containing annotation classes to load
     * - 'annotationNamespaces'    - [optional] Array of arrays containing a namespace as the first value and a
     *                               directory as the second value to autoload entity classes from.  May be a single
     *                               array, or an array of arrays.
     *
     * @param array $options Array of config options
     *
     * @throws InvalidOptionValueException if 'annotationMetadataPaths' option is missing, or is not an array
     * @throws InvalidOptionValueException if an option
     */
    function __construct(array $options)
    {
        $this->setOptions(
            $options,
            [
                self::OPTION_SETTINGS_KEY => self::DEFAULT_SETTINGS_KEY,
                self::OPTION_SERVICE_NAME => self::DEFAULT_SERVICE_NAME
            ]
        );

        $this->validateOptionAnnotationPaths();

        $this->validateOptionsAnnotationLoaders();
    }


    /**
     * Call
     *
     * Perform actions specific to this middleware and optionally call the next downstream middleware.
     */
    public function call()
    {
        $this->setupService();

        $this->next->call();
    }


    /**
     * Adds the EntityManager, as a service, to the Slim container
     *
     * @throws MissingSettingException
     */
    public function setupService()
    {
        $app = $this->getApplication();

        if ($this->hasOption(self::OPTION_ANNOTATIONS_AUTOLOADERS)) {
            foreach ($this->getOption(self::OPTION_ANNOTATIONS_AUTOLOADERS) as $autoloader) {
                AnnotationRegistry::registerLoader($autoloader);
            }
        }
        if ($this->hasOption(self::OPTION_ANNOTATIONS_FILES)) {
            foreach ($this->getOption(self::OPTION_ANNOTATIONS_FILES) as $file) {
                AnnotationRegistry::registerFile($file);
            }
        }
        if ($this->hasOption(self::OPTION_ANNOTATIONS_NAMESPACES)) {
            foreach ($this->getOption(self::OPTION_ANNOTATIONS_NAMESPACES) as $namespaceMapping) {
                // This might need amending, as not tested yet
                AnnotationRegistry::registerAutoloadNamespace($namespaceMapping[0], $namespaceMapping[1]);
            }
        }

        $isDevMode = !!$app->config('debug');

        $config = Setup::createConfiguration($isDevMode);
        $config->setMetadataDriverImpl(
            $config->newDefaultAnnotationDriver($this->getOption(self::OPTION_ANNOTATION_PATHS), false)
        );

        // Use lowercase underscore naming convention
        $config->setNamingStrategy(new UnderscoreNamingStrategy(CASE_LOWER));

        $dbSettingsKey = $this->getOption('settingsKey');
        if (!isset($app->container['settings'][$dbSettingsKey])) {
            throw MissingSettingException::build([], ['setting' => $dbSettingsKey]);
        }
        $connectionParams = $app->container['settings'][$dbSettingsKey];

        if ($isDevMode) {
            $connectionParams['dbname'] .= '_' . $app->getMode();
        }

        $app->container->singleton(
            $this->getOption('serviceName'),
            function () use ($connectionParams, $config) {
                return EntityManager::create($connectionParams, $config);
            }
        );
    }


    /**
     * Validates the OPTION_ANNOTATION_PATHS option is present and is an array
     *
     * @throws InvalidOptionValueException
     */
    protected function validateOptionAnnotationPaths()
    {
        if (!$this->hasOption(self::OPTION_ANNOTATION_PATHS) || !is_array(
                $this->getOption(
                    self::OPTION_ANNOTATION_PATHS
                )
            )
        ) {
            $additionalProperties = [
                'option' => self::OPTION_ANNOTATION_PATHS,
                'value'  => null,
                'reason' => null,
            ];
            if (!$this->hasOption(self::OPTION_ANNOTATION_PATHS)) {
                $additionalProperties['reason'] = 'Must be provided';
            }
            if (!is_array($this->getOption(self::OPTION_ANNOTATION_PATHS))) {
                $additionalProperties['value'] = $this->getOption(self::OPTION_ANNOTATION_PATHS);
                $additionalProperties['reason'] = 'Must be an array';
            }
            throw InvalidOptionValueException::build(
                [],
                $additionalProperties
            );
        }
    }


    /**
     * Validates that each of the OPTION_ANNOTATIONS_* options are arrays, if providede
     *
     * @throws InvalidOptionValueException
     */
    protected function validateOptionsAnnotationLoaders()
    {
        $annotationOptions = [
            self::OPTION_ANNOTATIONS_AUTOLOADERS,
            self::OPTION_ANNOTATIONS_FILES,
            self::OPTION_ANNOTATIONS_NAMESPACES,
        ];
        foreach ($annotationOptions as $annotationOption) {

            if (!$this->hasOption($annotationOption)) {
                continue;
            }

            $optionValue = $this->getOption($annotationOption);
            if (!is_array($optionValue)) {
                throw InvalidOptionValueException::build(
                    [],
                    [
                        'option' => $annotationOption,
                        'value'  => $optionValue,
                        'reason' => 'Must be an array',
                    ]
                );
            }
        }
    }
}