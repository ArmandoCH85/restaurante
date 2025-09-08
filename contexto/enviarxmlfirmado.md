Endpoints Firma
Enviar XML firmado
Se enviará el XML en base64 obteniéndose el CDR en base64.

Firmar XML
POST {{url}}/api/cpe/enviar

Headers

Name
Value
Accept

application/json

Content-Type

application/json

Authorization

Bearer {{token_acceso}}

Body

Name
Type
Description
nombre_xml_firmado

String

Nombre del archivo XML firmado

contenido_xml_firmado

String

XML firmado en base64
"token_acceso": esta en el archivo obtenertoken.md