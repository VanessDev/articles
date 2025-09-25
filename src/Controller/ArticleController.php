<?php

namespace App\Controller;


use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/articles')]
final class ArticleController extends AbstractController
{
    //afficher tous les articles
    #[Route('/', name: 'Articles', methods: 'GET')]
    public function index(ArticleRepository $articlerepo)
    {
        $articles = $articlerepo->findBy(['createdAt'], ['DESC']);
        //$articles = $articlerepo->findBy (['article' => $article]);

        
        return $this->render('articles/index.html.twig', [
            'articles' => $articles
        ]);
    }
// afficher les articles par leur ID
    #[Route('/show/{id}', name: "article_show", methods: 'GET')]
    public function show(ArticleRepository $ArticleRepository, int $id)
    {
        $article = $ArticleRepository->findOneBy(['id' => $id]);
        return $this->render('articles/articleshow.html.twig', [
            'article' => $article
        ]);
    }

   
    #[Route('/new', name: 'article_new')]
    public function new(Request $REQUEST, EntityManagerInterface $em)
    {
        
        $article = new Article();

        $formArticle = $this->createForm(ArticleType::class, $article);
        
        $formArticle->handleRequest($REQUEST);
       
        if ($formArticle->isSubmitted() && $formArticle->isValid()) {
            //comment avoir la date immutable de now
            $date = new DateTimeImmutable();
            $article->setCreatedAt($date);
            //ajouter la date dans mon article que je viens de creer

            // dd($date, $article);
            $em->persist($article);
            
            $em->flush();   

            $this->addFlash('success', 'bravo votre article a ete créé');
            
           
            return $this->redirectToRoute('Articles');
        }
        //renvoie le formulaire à la view (url)
        return $this->render('articles/articlenew.html.twig', [

            'formArticle' => $formArticle
        ]);

    }

    #[Route('/delete/{id}', name: 'article_delete')]
    public function delete(int $id, Request $request, Article $article, EntityManagerInterface $em)
    {

        if ($this->isCsrfTokenValid('delete' . $id, $request->request->get('_token'))) {
            $em->remove($article);
            $em->flush();
             $this->addFlash('success', 'bravo votre article a ete supprimé');

            return $this->redirectToRoute('Articles');
        } else {
            $this->addFlash('error','echec de la suppression');
            return$this->redirectToRoute('Articles');
          
        }
    }


    #[Route('/{id}/edit', name: 'article_edit')]
   
    public function edit(Article $article, Request $request, EntityManagerInterface $em)
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'bravo votre article a ete modifié');
            return $this->redirectToRoute('Articles');
        }
        return $this->render('articles/edit.html.twig', [
            'form' => $form

        ]);

    }
}




