<?php

namespace App\Controller\Admin;

use App\Entity\Logs;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class LogsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Logs::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->renderSidebarMinimized()->setSearchFields(['name', 'description']);
    }


}
