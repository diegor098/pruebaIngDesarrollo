<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;

class DefaultController extends Controller
{
    
     /**
     * @Route("/", name="homepage")
     */
    public function rootAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/place.html.twig');
    }
    //Función para llamar al index del formulario
    /**
     * @Route("/index", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/place.html.twig');
    }

    //Funcion para hacer la petición de pago a la API de PlacetoPay
     /**
     * @Route("/place", name="place")
     */
    public function placeAction(Request $request)
    {
        //datos recogidos por post enviados desde la vista place.html.twig (app/Resources/views/default/place.html.twig)
        $identificacion=$request->get('identificacion',null);
        $nombres=$request->get('nombres',null);
        $apellidos=$request->get('apellidos',null);
        $direccion=$request->get('direccion',null);
        $ciudad=$request->get('ciudad',null);
        $pais="CO";
        $valorapagar=$request->get('valorapagar',null);
        $email=$request->get('email',null);
        $celular=$request->get('celular',null);

        //claves de acceso enviadas por PLACETOPAY
        $secretKey="024h1IlD";
        $identificador="6dd490faf9cb87a9862245da41170ff2";
        
        //generando variables para la autenticación

        //seed
        $seed = date("c");

        //nonce
        if (function_exists('random_bytes')) {
            $nonce = bin2hex(random_bytes(16));
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $nonce = bin2hex(openssl_random_pseudo_bytes(16));
        } else {
            $nonce = mt_rand();
        }
        
        // trnakey
        $tranKey = base64_encode(sha1($nonce . $seed . $secretKey, true));

        //referencia de orden (aleatorio)
        $reference = rand(1000,5000);

        //nonce base 64
        $nonceBase64 = base64_encode($nonce);

        //expiracion
        $expiracion=date('c', strtotime('+2 days'));

        //Json con los campos para enviar la petición
        $data=[
            "auth"=>[
                "login"=> $identificador,
                "seed"=> $seed,
                "nonce"=> $nonceBase64,
                "tranKey"=> $tranKey
            ],
            "locale"=>"es_CO",
            "buyer"=>[
                "document"=> $identificacion,
                "documentType"=> "CC",
                "name"=> $nombres,
                "surname"=> $apellidos,
                "email"=> $email,
                "mobile"=> $celular,
                "address"=>[
                    "street"=> $direccion,
                    "city"=> $ciudad,
                    "country"=> $pais
                ]
            ],
            "payment"=>[
                "reference"=> $reference,
                "description"=> "Pago del usuario",
                "amount"=>[
                    "currency"=> "COP",
                    "total"=> $valorapagar
                ],
                "allowPartial"=> false
            ],
            "expiration"=> $expiracion,
            "returnUrl"=> "http://localhost:8000/placeres?reference=".$reference,
            "userAgent"=> "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.82 Safari/537.36",
            "ipAddress"=> "127.0.0.1"
        ];

        //enviar petición post a la API de placetopay
        $ch = curl_init("https://test.placetopay.com/redirection/api/session");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //establecemos el verbo http que queremos utilizar para la petición
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        //enviamos el array data
        curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($data));
        //obtenemos la respuesta
        $response = curl_exec($ch);
        // Se cierra el recurso CURL y se liberan los recursos del sistema
        curl_close($ch);
        if(!$response) {
            $this->addFlash(
                'error',
                'No se pudo procesar el pago, intentelo nuevamente !!'
            );
            return $this->render('default/place.html.twig');
        }else{
            //obtenemos la respuesta
            $response=json_decode($response);

            //url enviada para la redirección
            $url=$response->processUrl;

            //obtenemos el requestiD enviado
            $id=$response->requestId;

            //iniciamos una sesión para guardar el requestiD y posteriormente obtenerlo en el contrlador "placeresAction"
            $session = new Session();

            if(!isset($_SESSION)) $session->start();

            // Establecer una variable con el nombre requestId
            $session->set('requestId', $id);
            
            //redireccionar a la url enviada
            $redireccion = new RedirectResponse($url);

            return $redireccion;
        }
    }

    //Función para consultar con el requestId si el pago fue o no exitoso a la API de PlacetoPay
     /**
     * @Route("/placeres", name="placeres")
     */
    public function placeresAction(Request $request)
    {
        //referencia de orden
        $reference=$request->query->get('reference',null);

        //obtener de la sesión el requestiD
        $session = $request->getSession();
        //requestid
        $requestId=$session->get('requestId');
        
        if($requestId != null){

        //claves de acceso enviadas por PLACETOPAY
        $secretKey="024h1IlD";
        $identificador="6dd490faf9cb87a9862245da41170ff2";
        
        //generando variables para la autenticación

        //seed
        $seed = date("c");

        //nonce
        if (function_exists('random_bytes')) {
            $nonce = bin2hex(random_bytes(16));
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $nonce = bin2hex(openssl_random_pseudo_bytes(16));
        } else {
            $nonce = mt_rand();
        }
        
        // trnakey
        $tranKey = base64_encode(sha1($nonce . $seed . $secretKey, true));

        //nonce base 64
        $nonceBase64 = base64_encode($nonce);
    
            $data=[
                "auth"=>[
                    "login"=> $identificador,
                    "seed"=> $seed,
                    "nonce"=> $nonceBase64,
                    "tranKey"=> $tranKey
                ]
            ];
            
            //enviar petición post a la API de placetopay para consultar la respuesta si fue (Aprobado, pendiente, fallido o rechazado).
            $urlreq="https://test.placetopay.com/redirection/api/session/".$requestId;
            $ch = curl_init($urlreq);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            //establecemos el verbo http que queremos utilizar para la petición
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            //enviamos el array data
            curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($data));
            //obtenemos la respuesta
            $response = curl_exec($ch);
            // Se cierra el recurso CURL y se liberan los recursos del sistema
            curl_close($ch);
            if(!$response) {
                $this->addFlash(
                    'error',
                    'No se pudo consultar si su pago fue realizado con éxito, intente nuevamente !!'
                );
                return $this->render('default/place.html.twig');
            }else{
                $response=json_decode($response);
                //definiendo datos relevantes de la respuesta de la API de placetopay
                $res=[
                    "referencia"=>$response->payment[0]->internalReference,
                    "estado"=>$response->status->status,
                    "banco"=>$response->payment[0]->issuerName,
                    "valor" => $response->payment[0]->amount->to->total,
                    "fecha"=>$response->status->date,
                    "cedula"=>$response->request->payer->document,
                    "nombres"=>$response->request->payer->name,
                    "apellidos"=>$response->request->payer->surname,
                    "email"=>$response->request->payer->email,
                    "celular"=>$response->request->payer->mobile,
                ];
                //definiendo mensaje de confirmacion
                if($response->status->status != "APPROVED"){
                    $this->addFlash(
                        'rechazado',
                        'No se pudo realizar el pago exitosamente, Estado '.$response->status->status
                    );
                }else{
                $this->addFlash(
                    'exito',
                    'Se realizó tu pago con éxito !!'
                );
                }
                //limpiar session del requestId
                $session->clear();
                //
                return $this->render('default/place.html.twig',array("respuestas"=>$res));
            }


    }else{
        $this->addFlash(
            'error',
            'El requestId es nulo, intentalo nuevamente !!'
        );
        return $this->render('default/place.html.twig');

    }
    }
}
