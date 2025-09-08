Endpoints Firma
Obtener Token
Permitirá obtener el token_acceso para los siguiente procesos, el tiempo de expiración del token_acceso es en segundos.

Obtener Token
POST {{url}}/api/auth/cpe/token

Headers

Name
Value
Accept

application/json

Content-Type

application/json

Body

Name
Type
Description
usuario: soporte@sitech.site

string:

Obtenido al crear la empresa

contraseña: },99,ordaNA

string

Obtenido al crear la empresa

Response

200
Copy
{
    "token_acceso": "3|r4pm6Cqo9NyNJHsuKRdm5yUb6JMIwQ6L3yZZQaXb05b0ad20",
    "expira_en": "600"
}