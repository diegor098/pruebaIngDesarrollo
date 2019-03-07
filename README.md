Documentación de la prueba
==============================

El desarrollo esta hecho bajo el framework PHP Symfony, utlizando el MVC (Modelo, Vista, Controlador) que maneja este framework.

==================
Ejecución
==================

Para ejecutar el proyecto, primero se debe lanzar el servidor que trae Symfony,
se debe ejecutar el siguiente comando en consola, situandose en la carpeta del proyecto /placetopay:

php bin/console server:run

Una vez lanzado el comando, se debe ingresar a la siguiente ruta en el navegador para
la posterior vista del proyecto

http://localhost:8000/index

============
Paso a paso
============
- Se mostrará un formulario en el cual se deben llenar todos los campos, el valor que desea cancelar,
y hacer click en Realizar pago
- Será redirigido a la pagina de placetopay para realizar el pago.
- Se debe seleccionar para prueba el metodo de pago (PSE)
- Se listará los bancos, donde se debe seleccionar el Banco Union Colombiano
- Será redirigido al banco de pruebas donde debe dar click en el boton debug sin llenar ningun campo
de los listados en la pagina.
- Una vez de click, será redirigido a otra pagina de pruebas donde debe poner en el campo transactionState la misma fecha que aparece en el campo bankProcessDate (Copiar y Pegar), y debe poner en el campo authorizationID el numero 12
- Una vez llenos los campos mencionados, debe dar click en el boton Call, le aparecera un mensaje en letras rojas, una vez aparezca el mensaje, debe dar click en el boton Return to PPE
- Será redirigido a la pagina de confirmacion de placetopay, donde vera alguna información sobre el pago que acabó de realizar, a continuación debe dar click en el boton "Volver a la página del comercio", donde será redirigido a la pagina que se desarrollo teniendo la confirmación del pago que se hizo, y los datos relevantes del pago.

==========
Notas
==========

Las siguientes son las rutas donde esta el código fuente del Controlador, y la Vista

Controlador: /placetopay/src/AppBundle/DefaultController

Nota: Cada metodo que tiene el controlador mencionado en la ruta,esta documentado para su posterior analisis.

Vista: /placetopay/app/Resources/views/default/place.html.twig

===================================

Autor: Ing. Diego Rosero
Contacto: lcdiego098@hotmail.com
PASTO - COLOMBIA






