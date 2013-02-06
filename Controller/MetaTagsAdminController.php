<?php

namespace Copiaincolla\MetaTagsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Copiaincolla\MetaTagsBundle\Entity\Metatag;

/**
 * MetaTag controller.
 *
 * @Route("/metatags")
 */
class MetaTagsAdminController extends Controller
{
    /**
     * @Route("/", name="admin_metatag")
     * @Template("CopiaincollaMetaTagsBundle:MetaTagsAdmin:index/index.html.twig")
     */
    public function indexAction()
    {
        return array(
            'entities' => $this->container->get('ci_metatags.meta_tags_admin')->getManagedMetaTags()
        );
    }

    /**
     * Displays information about a MetaTag entity.
     *
     * @Route("/show/{id}", name="admin_metatag_show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('CopiaincollaMetaTagsBundle:Metatag')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Metatag entity.');
        }

        return array(
            'entity' => $entity,
            'associated_route' => $this->container->get('ci_metatags.route_exposer')->getRouteByUrl($entity->getUrl())
        );
    }

    /**
     * Displays a form to create a new MetaTag entity.
     *
     * @Route("/new", name="admin_metatag_new")
     * @Template("CopiaincollaMetaTagsBundle:MetaTagsAdmin:edit.html.twig")
     */
    public function newAction()
    {
        $entity = new Metatag();

        $url = $this->getRequest()->get('url');
        $entity->setUrl($url);

        $form = $this->createForm($this->container->get('ci_metatags.metatag_formtype'), $entity);

        return array(
            'entity' => $entity,
            'associated_route' => $this->container->get('ci_metatags.route_exposer')->getRouteByUrl($url),
            'form' => $form->createView()
        );
    }

    /**
     * Creates a new Metatag entity.
     *
     * @Route("/create", name="admin_metatag_create")
     * @Method("post")
     * @Template("CopiaincollaMetaTagsBundle:MetaTagsAdmin:edit.html.twig")
     */
    public function createAction()
    {
        $entity = new Metatag();

        $request = $this->getRequest();

        $form = $this->createForm($this->container->get('ci_metatags.metatag_formtype'), $entity);

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();

            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_metatag_edit', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView()
        );
    }

    /**
     * Displays a form to edit an existing Metatag entity.
     *
     * @Route("/{id}/edit", name="admin_metatag_edit")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('CopiaincollaMetaTagsBundle:Metatag')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Metatag entity.');
        }

        $editForm = $this->createForm($this->container->get('ci_metatags.metatag_formtype'), $entity);

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity' => $entity,
            'associated_route' => $this->container->get('ci_metatags.route_exposer')->getRouteByUrl($entity->getUrl()),
            'form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Metatag entity.
     *
     * @Route("/{id}/update", name="admin_metatag_update")
     * @Method("post")
     * @Template("CopiaincollaMetaTagsBundle:MetaTagsAdmin:edit.html.twig")
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('CopiaincollaMetaTagsBundle:Metatag')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Metatag entity.');
        }

        $editForm = $this->createForm($this->container->get('ci_metatags.metatag_formtype'), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_metatag_edit', array('id' => $id)));
        }

        return array(
            'entity' => $entity,
            'form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Display a form to delete a MetaTag entity
     *
     * @Template()
     *
     * @param $id
     * @return array
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function _deleteFormAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('CopiaincollaMetaTagsBundle:Metatag')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Metatag entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity' => $entity,
            'delete_form' => $deleteForm->createView()
        );
    }

    /**
     * Deletes a Metatag entity.
     *
     * @Route("/{id}/delete", name="admin_metatag_delete")
     * @method("POST")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('CopiaincollaMetaTagsBundle:Metatag')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Metatag entity.');
        }

        $em->remove($entity);
        $em->flush();

        return $this->redirect($this->generateUrl('admin_metatag'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
