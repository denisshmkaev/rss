<?php

namespace App\Controller\Admin;

use App\Entity\News;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class NewsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return News::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->renderSidebarMinimized();
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            'id',
            'short_description',
            'author',
            'publications_date',
        ];
    }
}
