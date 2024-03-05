<?php

namespace App\IntraModule\Presenters;

use App\Components\UblabooTable\Model\ACLGrid;
use App\Model\Database\Entity;
use Nette\Caching\Cache;
use Nette\Utils\DateTime;

class TranslationPresenter extends BasePresenter
{
    /**
     * ACL name='Překlady'
     * ACL rejection='Nemáte přístup k překladům.'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, Entity\PermissionItem::TYPE_PRESENTER);
    }

    /**
     * ACL name='Tabulka s přehledem uživatelů'
     */
    public function createComponentTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(Entity\Translation::class, get_class(), __FUNCTION__);

        $column = $grid->getColumn('message');
        if ($column) {
            $column->setEditableCallback(function($id, $value) use ($grid) {
                $ent = $this->em->getTranslationRepository()->find($id);
                if ($ent) {
                    $ent->setMessage($value);
                    $ent->setUpdateAt(new DateTime());
                    $this->em->persist($ent);
                    $this->em->flush();
                    $this->flashMessage('Překlad byl upraven.', 'info');
                    //$this->translator->catalogueCompiler->invalidateCache();
                }
                $grid->redrawItem($id);
            });
        }

        return $grid;
    }
}