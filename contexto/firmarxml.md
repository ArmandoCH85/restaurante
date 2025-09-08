Firmar XML
Se enviará el XML sin firmar en base64 devolviendo el XML firmado en base64 con el certificado PSE; este proceso es para todos los documentos.

Firmar XML
POST {{url}}/api/cpe/generar

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
tipo_integracion

Number

Valor 0

nombre_archivo

String

Nombre del archivo XML

contenido_archivo

String

XML en base64

Response

200
Copy
{
  "estado": 200,
  "xml": "PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiIHN0YW5kYW...",
  "codigo_hash": "vEZR9aRkrRc02s9PfpL//TmPFbA=",
  "mensaje": "XML firmado correctamente",
  "external_id": "963704d1-fc6b-412f-8fb5-818649214ef6"
}