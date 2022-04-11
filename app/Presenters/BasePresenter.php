<?php

declare(strict_types=1);

namespace App\Presenters;

use Contributte\MenuControl\UI\IMenuComponentFactory;
use Contributte\MenuControl\UI\MenuComponent;
use Nette;
use RedBeanPHP\R;
use RedBeanPHP\ToolBox;

class BasePresenter extends Nette\Application\UI\Presenter
{
    protected ToolBox $toolBox;

    protected IMenuComponentFactory $menuFactory;

    public function __construct()
    {
        $this->absoluteUrls = true;
    }

    public function injectToolBox(ToolBox $toolBox)
    {
        $this->toolBox = $toolBox;
        R::freeze(true);
    }

    public function injectMenu(IMenuComponentFactory $menu)
    {
        $this->menuFactory = $menu;
    }

    protected function createComponentMenu(): MenuComponent
    {
        return $this->menuFactory->create('default');
    }

    protected function getResource()
    {
        return sprintf('%s:%s', $this->getName(), $this->getAction());
    }
}
