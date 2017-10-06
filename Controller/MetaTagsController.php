<?php

namespace Copiaincolla\MetaTagsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * MetaTag controller
 */
class MetaTagsController extends Controller
{
    /**
     * Render HTML metatags
     *
     * @param $vars array of template variables
     * @param $inlineMetatags array of override metatags
     */
    public function renderAction($vars = array(), $inlineMetatags = array())
    {
        $metatagsLoader = $this->container->get('ci_metatags.metatags_loader');

        $request = Request::createFromGlobals();

        return $this->render("CopiaincollaMetaTagsBundle:MetaTags:render.html.twig", array(
            'metatags'  => $metatagsLoader->getMetaTagsForRequest($request, $inlineMetatags),
            'vars'      => $vars
        ));
    }

}