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
 * @Route("/admin/datasource")
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
            'id'        => 'ID',
            'username'  => 'Name',
            'domain'    => 'Domain',
            'port'      => 'Port',
            'username'  => 'Username',
            'password'  => 'Password',
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
     * @Method("GET")
     */
    public function indexAction()
    {
        return $this->doIndex();
    }

    /**
     * Creates a new DataSource entity.
     *
     * @Route("/", name="admin_datasource_create")
     * @Method("POST")
     */
    public function createAction(Request $request)
    {
        return $this->doCreate($request);
    }

    /**
     * Displays a form to create a new DataSource entity.
     *
     * @Route("/new", name="admin_datasource_new")
     * @Method("GET")
     */
    public function newAction()
    {
        return $this->doNew();
    }

    /**
     * Displays a form to edit an existing DataSource entity.
     *
     * @Route("/{id}/edit", name="admin_datasource_edit")
     * @Method("GET")
     */
    public function editAction($id)
    {
        return $this->doEdit($id);
    }

    /**
     * Edits an existing DataSource entity.
     *
     * @Route("/{id}", name="admin_datasource_update")
     * @Method("GET|POST|PUT")
     */
    public function updateAction(Request $request, $id)
    {
        return $this->doUpdate($request, $id);
    }

    /**
     * Deletes a DataSource entity.
     *
     * @Route("/{id}/delete", name="admin_datasource_delete")
     * @Method("GET|DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        return $this->doDelete($request, $id);
    }
}
