<?php

namespace Athorrent\Routing;

use Symfony\Component\Routing\Generator\Dumper\GeneratorDumper;
use Symfony\Component\Routing\RouteCollection;

class PhpGeneratorDumper extends GeneratorDumper
{
    private $actionMap;

    public function __construct(RouteCollection $routes, ActionMap $actionMap)
    {
        parent::__construct($routes);
        $this->actionMap = $actionMap;
    }

    /**
     * Dumps a set of routes to a PHP class.
     *
     * Available options:
     *
     *  * class:      The class name
     *  * base_class: The base class name
     *
     * @param array $options An array of options
     *
     * @return string A PHP class representing the generator class
     */
    public function dump(array $options = array())
    {
        $options = array_merge([
            'class' => 'ProjectUrlGenerator',
            'base_class' => 'Symfony\\Component\\Routing\\Generator\\UrlGenerator',
        ], $options);

        return <<<EOF
<?php

use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Psr\Log\LoggerInterface;

/**
 * This class has been auto-generated
 * by the Symfony Routing Component.
 */
class {$options['class']} extends {$options['base_class']}
{
    private static \$declaredRoutes;
    
    private static \$declaredActionMap;
    
    public function __construct(RequestContext \$context, LoggerInterface \$logger = null, string \$defaultLocale = null)
    {
        if (null === self::\$declaredRoutes) {
            self::\$declaredRoutes = {$this->generateDeclaredRoutes()};
            self::\$declaredActionMap = {$this->generateActionMap()};
        }
        
        \$this->context = \$context;
        \$this->logger = \$logger;
        
        \$this->defaultLocale = \$defaultLocale;
        \$this->actionMap = self::\$declaredActionMap;
    }
    
{$this->generateGenerateMethod()}
}
EOF;
    }

    /**
     * Generates PHP code representing an array of defined routes
     * together with the routes properties (e.g. requirements).
     *
     * @return string PHP code
     */
    private function generateDeclaredRoutes()
    {
        $routes = "[\n";

        foreach ($this->getRoutes()->all() as $name => $route) {
            $compiledRoute = $route->compile();

            $properties = [];
            $properties[] = $compiledRoute->getVariables();
            $properties[] = $route->getDefaults();
            $properties[] = $route->getRequirements();
            $properties[] = $compiledRoute->getTokens();
            $properties[] = $compiledRoute->getHostTokens();
            $properties[] = $route->getSchemes();

            $routes .= sprintf("        '%s' => %s,\n", $name, var_export($properties, true));
        }

        $routes .= '    ]';

        return $routes;
    }

    private function generateActionMap()
    {
        $actionMap = "[\n";

        foreach ($this->actionMap as $action => $prefixIds) {
            $actionMap .= sprintf("        '%s' => %s,\n", $action, var_export($prefixIds, true));
        }

        $actionMap .= '    ]';

        return $actionMap;
    }

    /**
     * Generates PHP code representing the `generate` method that implements the UrlGeneratorInterface.
     *
     * @return string PHP code
     */
    private function generateGenerateMethod()
    {
        return <<<'EOF'
    public function generate($name, $parameters = [], $referenceType = self::ABSOLUTE_PATH)
    {
        $locale = $parameters['_locale']
            ?? $this->context->getParameter('_locale')
            ?: $this->defaultLocale;
        
        if (null !== $locale && (self::$declaredRoutes[$name . '.' . $locale][1]['_canonical_route'] ?? null) === $name) {
            unset($parameters['_locale']);
            $name .= '.' . $locale;
        } elseif (!isset(self::$declaredRoutes[$name])) {
            $prefixId = $this->getPrefixId($name, $parameters);

            if ($locale === $this->defaultLocale) {
                $name = $prefixId . $name;
                unset($parameters['_locale']);
            } else {
                $name = $prefixId . $name . '|i18n';
                $parameters['_locale'] = $locale;
            }
            
            if (!isset(self::$declaredRoutes[$name])) {
                throw new RouteNotFoundException(sprintf('Unable to generate a URL for the named route "%s" as such route does not exist.', $name));
            }
        }
        
        list($variables, $defaults, $requirements, $tokens, $hostTokens, $requiredSchemes) = self::$declaredRoutes[$name];
        
        return $this->doGenerate($variables, $defaults, $requirements, $tokens, $parameters, $name, $referenceType, $hostTokens, $requiredSchemes);
    }
EOF;
    }
}