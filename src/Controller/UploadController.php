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

        $nomMesureGlobale="Consommation Globale";
        $consoMesureGlobale=0.0;
        $dureeMesureGlobale=0;


        foreach ($objets as $data) {
              $hote = $data->host;
              $consommateurs = $data->consumers;
              if (isset($hote->consumption))
              {
              $consoMesureGlobale=$consoMesureGlobale+$hote->consumption;
              }
              if (isset($hote->timestamp))
              $dureeMesureGlobale=$dureeMesureGlobale+$hote->timestamp;

              $consommateurs = $data->consumers;
              $tableauConso = Array();
              foreach ($consommateurs as $consommateur) {
                $tableauConso=$tableauConso+Array(
                    $consommateur->exe=> array (
                      'nom'=>$consommateur->exe,
                      'conso'=>$consommateur->consumption,
                      'duree'=>$consommateur->timestamp,
                    )
                  );
              }
              $json = json_encode($tableauConso);
$bytes = file_put_contents("../var/uploads/verif.json", $json);
        }

        return $this->render('home/dashboard.html.twig',['nomMesureGlobale'=>$nomMesureGlobale,'consoMesureGlobale'=>$consoMesureGlobale,'dureeMesureGlobale'=>$dureeMesureGlobale,'tabConso'=>$tableauConso],);
    }
}
