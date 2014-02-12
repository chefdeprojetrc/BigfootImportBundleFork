<?php

namespace Bigfoot\Bundle\ImportBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

use Bigfoot\Bundle\CoreBundle\Controller\CrudController;
use Bigfoot\Bundle\CoreBundle\Theme\Menu\Item;
use Bigfoot\Bundle\ImportBundle\Entity\DataSource;
use Bigfoot\Bundle\ImportBundle\Form\DataSourceType;

/**
 * DataSource controller.
 *
 * @Cache(maxage="0", smaxage="0", public="false")
 * @Route("/datasource")
 */
class DataSourceController extends CrudController
{
    /**
     * Used to generate route names.
     * The helper method of this class will use routes named after this name.
     * This means if you extend this class and use its helper methods, if getName() returns 'my_controller', you must implement a route named 'my_controller'.
     *
     * @return string
     */
    protected function getName()
    {
        return 'admin_datasource';
    }

    /**
     * Must return the entity full name (eg. BigfootCoreBundle:Tag).
     *
     * @return string
     */
    protected function getEntity()
    {
        return 'BigfootImportBundle:DataSource';
    }

    /**
     * Must return an associative array field name => field label.
     *
     * @return array
     */
    protected function getFields()
    {
        return array(
            'id'       => 'ID',
            'username' => 'Name',
            'domain'   => 'Domain',
            'port'     => 'Port',
            'username' => 'Username',
            'password' => 'Password',
        );
    }

    protected function getFormType()
    {
        return 'bigfoot_bundle_importbundle_datasourcetype';
    }

    /**
     * Lists all DataSource entities.
     *
     * @Route("/", name="admin_datasource")
     */
    public function indexAction()
    {
        return $this->doIndex();
    }

    /**
     * Displays a form to create a new DataSource entity.
     *
     * @Route("/new", name="admin_datasource_new")
     */
    public function newAction(Request $request)
    {
        return $this->doNew($request);
    }

    /**
     * Displays a form to edit an existing DataSource entity.
     *
     * @Route("/edit/{id}", name="admin_datasource_edit")
     */
    public function editAction(Request $request, $id)
    {
        return $this->doEdit($request, $id);
    }

    /**
     * Deletes a DataSource entity.
     *
     * @Route("/delete/{id}", name="admin_datasource_delete")
     */
    public function deleteAction(Request $request, $id)
    {
        return $this->doDelete($request, $id);
    }
}
