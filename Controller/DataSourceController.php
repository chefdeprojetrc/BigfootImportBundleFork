<?php

namespace Bigfoot\Bundle\ImportBundle\Controller;

use Bigfoot\Bundle\CoreBundle\Crud\CrudController;
use Bigfoot\Bundle\CoreBundle\Theme\Menu\Item;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Bigfoot\Bundle\ImportBundle\Entity\DataSource;
use Bigfoot\Bundle\ImportBundle\Form\DataSourceType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
     * @Template("BigfootCoreBundle:crud:index.html.twig")
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
     * @Template("BigfootCoreBundle:crud:new.html.twig")
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
     * @Template("BigfootCoreBundle:crud:new.html.twig")
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
     * @Template("BigfootCoreBundle:crud:edit.html.twig")
     */
    public function editAction($id)
    {
        return $this->doEdit($id);
    }

    /**
     * Edits an existing DataSource entity.
     *
     * @Route("/{id}", name="admin_datasource_update")
     * @Method("PUT")
     * @Template("BigfootCoreBundle:crud:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        return $this->doUpdate($request, $id);
    }

    /**
     * Deletes a DataSource entity.
     *
     * @Route("/{id}", name="admin_datasource_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        return $this->doDelete($request, $id);
    }

    /**
     * Creates a form to delete a DataSource entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    protected function createDeleteForm($id)
    {
        return $this->container->get('form.factory')->createBuilder('form', array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
