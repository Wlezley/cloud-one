<?php

namespace App\Presenters;

use Nette;
use Nette\Database\Explorer;

class BasePresenter extends Nette\Application\UI\Presenter
{
    /** @var Explorer @inject */
    public $db;

    public function __construct()
    {
    }
}
