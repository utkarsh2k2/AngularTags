<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Category;
use AppBundle\Entity\Tag;

//use Doctrine\Common\Collections\ArrayCollection;

class DefaultController extends Controller {

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request) {
        return $this->render('default/index.html.twig', array());
    }

    /**
     * @Route("/jsondata", options={"expose"=true}, name="my_route_to_json_data")
     */
    public function tagsAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
                        'SELECT t.id, t.tagname
                         FROM AppBundle:Tag t
                         WHERE t.id > :id
                         ORDER BY t.id ASC'
                )->setParameter('id', '0');

        $tagsdata = $query->getScalarResult();
//         var_dump($tags);
//         exit;
        $response = new Response(json_encode($tagsdata));
//        var_dump($response->getContent());
//        exit;
        $response->headers->set('Content-Type', 'application/json');
//        var_dump($response);
//        exit;
        return $response;
    }    
    
    /**
     * @Route("/formsubmit", options={"expose"=true}, name="my_route_to_submit")
     */
    public function submitAction(Request $request) {
        $jsonString = file_get_contents('php://input');
        $form_data = json_decode($jsonString, true);

//        $form_data = [
//            ['id' => 3, 'categoryname' => 'gold'],
//            [
//                ['id' => 1, 'tagname' => 'wifi'],
//                ['id' => 4, 'tagname' => 'geyser'],
//                ['id' => 2, 'tagname' => 'cable']
//            ]
//        ];
        
        $em = $this->getDoctrine()->getManager();

        // set category details
        $categoryId = $form_data[0]['id'];
        $category = $em->getRepository('AppBundle:Category')->findOneById($categoryId);
        
        // set tags
        $len = count($form_data[1]);

        for ($i = 0; $i < $len; $i++) {
            $tagId = $form_data[1][$i]['id'];
            $tag = $em->getRepository('AppBundle:Tag')->findOneById($tagId);
            $tag->addCategory($category);
            $category->addTag($tag);
        }
//        var_dump($category);
//        exit;

        // persist/save in database
        $em->persist($category);
        $em->flush();
    }

}
