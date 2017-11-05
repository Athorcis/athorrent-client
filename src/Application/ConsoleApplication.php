<?php

namespace Athorrent\Application;

use Athorrent\Command\CreateUserCommand;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Knp\Provider\ConsoleServiceProvider;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\HttpFoundation\Request;

class ConsoleApplication extends BaseApplication
{
    public function __construct()
    {
        parent::__construct();

        $this->register(new ConsoleServiceProvider(), [
            'console.name' => 'athorrent-frontend',
            'console.version' => '1.0.0',
            'console.project_directory' => ROOT_DIR
        ]);

        $this->initializeDoctrineHelpers();

        $this['console']->add(new CreateUserCommand());
    }

    protected function initializeDoctrineHelpers()
    {
        $helperSet = new HelperSet([
            'db' => new ConnectionHelper($this['db']),
            'em' => new EntityManagerHelper($this['orm.em'])
        ]);

        $this['console']->setHelperSet($helperSet);

        ConsoleRunner::addCommands($this['console']);
    }

    public function run(Request $request = null)
    {
        return $this['console']->run();
    }
}
