<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\FileUploader;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use App\Entity\Mesure;
use Doctrine\Persistence\ObjectManager;

class UploadController extends AbstractController
{

    /**
     * @Route("/doUpload", name="do-upload")
     * @param Request $request
     * @param string $uploadDir
     * @param FileUploader $uploader
     * @param LoggerInterface $logger
     * @return Response
     */
    public function index(Request $request, string $uploadDir,
                          FileUploader $uploader, LoggerInterface $logger): Response
    {
        $finder = new Finder();
        $dossier = '../var/uploads';
        $ouverture=opendir($dossier);
        $fichier=readdir($ouverture);
        $fichier=readdir($ouverture);
        while ($fichier=readdir($ouverture)) {
        unlink("$dossier/$fichier");
        }
        closedir($ouverture);

        $token = $request->get("token");

        if (!$this->isCsrfTokenValid('upload', $token))
        {
            $logger->info("CSRF failure");

            return new Response("Operation not allowed",  Response::HTTP_BAD_REQUEST,
                ['content-type' => 'text/plain']);
        }

        $file = $request->files->get('myfile');

        if (empty($file))
        {
            return new Response("No file specified",
               Response::HTTP_UNPROCESSABLE_ENTITY, ['content-type' => 'text/plain']);
        }

        $filename = $file->getClientOriginalName();
        $uploader->upload($uploadDir, $file, $filename);

        $objets = json_decode(file_get_contents('../var/uploads/bd.json'));
        $compteurNuméroMesure=0;
        $tableauHote=array();
        foreach ($objets as $mesure) {
          $tableauHote= $tableauHote + array(
            $compteurNuméroMesure => array(
              'conso'=>$mesure->host->consumption,
              'date'=>$mesure->host->timestamp,
            )
          );
          $compteurNuméroMesure++;
        }
        $tableauConso=array();
        $compteurNuméroMesure=0;
        foreach ($objets as $mesure) {
          $consommateurs = $mesure->consumers;
          foreach ($consommateurs as $consommateur) {
            $compteurTaille = 0;
            foreach ($tableauConso as $consommateursDansTableau)
             {
               if ($consommateursDansTableau!=$consommateur->exe)
               {
                 $compteurTaille ++;
               }
            }
            if ($compteurTaille == sizeof($tableauConso))
            {
              $tableauConso= array_merge($tableauConso, array (
                  $compteurNuméroMesure=>$consommateur->exe,
              )
            );
              $compteurNuméroMesure++;
            }
          }
        }
        $tableauFinal =array();
        for ($i=0;$i<sizeof($tableauConso)-1;$i++){
          $cpt=0;
          foreach ($objets as $mesure){
            foreach ($mesure->consumers as $appli){
              if ($tableauConso[$i]==$appli->exe){
              $tableauFinal=array_merge_recursive($tableauFinal,array(
                  $cpt=> array (
                      'name'=>$appli->exe,
                    'conso'=>$appli->consumption,
                    'heure'=>$appli->timestamp,
                  )
              ));
              $cpt ++;
            }
          }
        }
        }


        /*$json = json_encode($tableauHote);
        $bytes = file_put_contents("../var/uploads/verif.json", $json);
        $json = json_encode($tableauConso);
        $bytes = file_put_contents("../var/uploads/verif3.json", $json);
        $json = json_encode($tableauFinal);
        $bytes = file_put_contents("../var/uploads/verif2.json", $json);*/


        return $this->render('home/dashboard.html.twig',['tableauHote'=>$tableauHote,'tableauConso'=>$tableauConso,'tableauFinal'=>$tableauFinal],);
    }
}
