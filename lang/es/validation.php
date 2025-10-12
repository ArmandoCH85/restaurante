<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mensajes de Validación en Español
    |--------------------------------------------------------------------------
    |
    | Las siguientes líneas de idioma contienen los mensajes de error predeterminados
    | utilizados por la clase validadora. Algunas de estas reglas tienen múltiples versiones
    | como las reglas de tamaño. Siéntete libre de ajustar cada uno de estos mensajes aquí.
    |
    */

    'accepted' => '🔘 Debes aceptar :attribute.',
    'accepted_if' => '🔘 Debes aceptar :attribute cuando :other sea :value.',
    'active_url' => '🌐 :attribute debe ser una URL válida.',
    'after' => '📅 :attribute debe ser una fecha posterior a :date.',
    'after_or_equal' => '📅 :attribute debe ser una fecha posterior o igual a :date.',
    'alpha' => '🔤 :attribute solo puede contener letras.',
    'alpha_dash' => '🔤 :attribute solo puede contener letras, números, guiones y guiones bajos.',
    'alpha_num' => '🔤 :attribute solo puede contener letras y números.',
    'array' => '📋 :attribute debe ser una lista.',
    'ascii' => '🔤 :attribute solo puede contener caracteres alfanuméricos y símbolos de un byte.',
    'before' => '📅 :attribute debe ser una fecha anterior a :date.',
    'before_or_equal' => '📅 :attribute debe ser una fecha anterior o igual a :date.',
    'between' => [
        'array' => '📋 :attribute debe tener entre :min y :max elementos.',
        'file' => '📁 :attribute debe pesar entre :min y :max kilobytes.',
        'numeric' => '🔢 :attribute debe estar entre :min y :max.',
        'string' => '📝 :attribute debe tener entre :min y :max caracteres.',
    ],
    'boolean' => '✅ :attribute debe ser verdadero o falso.',
    'can' => '🚫 :attribute contiene un valor no autorizado.',
    'confirmed' => '🔐 La confirmación de :attribute no coincide.',
    'contains' => '📝 :attribute falta un valor requerido.',
    'current_password' => '🔐 La contraseña es incorrecta.',
    'date' => '📅 :attribute debe ser una fecha válida.',
    'date_equals' => '📅 :attribute debe ser una fecha igual a :date.',
    'date_format' => '📅 :attribute debe coincidir con el formato :format.',
    'decimal' => '🔢 :attribute debe tener :decimal lugares decimales.',
    'declined' => '❌ :attribute debe ser rechazado.',
    'declined_if' => '❌ :attribute debe ser rechazado cuando :other sea :value.',
    'different' => '🔄 :attribute y :other deben ser diferentes.',
    'digits' => '🔢 :attribute debe tener :digits dígitos.',
    'digits_between' => '🔢 :attribute debe tener entre :min y :max dígitos.',
    'dimensions' => '🖼️ :attribute tiene dimensiones de imagen inválidas.',
    'distinct' => '🔄 :attribute tiene un valor duplicado.',
    'doesnt_end_with' => '📝 :attribute no debe terminar con ninguno de los siguientes: :values.',
    'doesnt_start_with' => '📝 :attribute no debe comenzar con ninguno de los siguientes: :values.',
    'email' => '📧 :attribute debe ser un correo electrónico válido. Ejemplo: usuario@gmail.com',
    'ends_with' => '📝 :attribute debe terminar con uno de los siguientes: :values.',
    'enum' => '🔘 El :attribute seleccionado es inválido.',
    'exists' => '🔍 El :attribute seleccionado no existe.',
    'extensions' => '📁 :attribute debe tener una de las siguientes extensiones: :values.',
    'file' => '📁 :attribute debe ser un archivo.',
    'filled' => '📝 :attribute debe tener un valor.',
    'gt' => [
        'array' => '📋 :attribute debe tener más de :value elementos.',
        'file' => '📁 :attribute debe ser mayor que :value kilobytes.',
        'numeric' => '🔢 :attribute debe ser mayor que :value.',
        'string' => '📝 :attribute debe tener más de :value caracteres.',
    ],
    'gte' => [
        'array' => '📋 :attribute debe tener :value elementos o más.',
        'file' => '📁 :attribute debe ser mayor o igual que :value kilobytes.',
        'numeric' => '🔢 :attribute debe ser mayor o igual que :value.',
        'string' => '📝 :attribute debe tener :value caracteres o más.',
    ],
    'hex_color' => '🎨 :attribute debe ser un color hexadecimal válido.',
    'image' => '🖼️ :attribute debe ser una imagen.',
    'in' => '🔘 El :attribute seleccionado es inválido.',
    'in_array' => '🔍 :attribute no existe en :other.',
    'integer' => '🔢 :attribute debe ser un número entero.',
    'ip' => '🌐 :attribute debe ser una dirección IP válida.',
    'ipv4' => '🌐 :attribute debe ser una dirección IPv4 válida.',
    'ipv6' => '🌐 :attribute debe ser una dirección IPv6 válida.',
    'json' => '📋 :attribute debe ser una cadena JSON válida.',
    'list' => '📋 :attribute debe ser una lista.',
    'lowercase' => '🔤 :attribute debe estar en minúsculas.',
    'lt' => [
        'array' => '📋 :attribute debe tener menos de :value elementos.',
        'file' => '📁 :attribute debe ser menor que :value kilobytes.',
        'numeric' => '🔢 :attribute debe ser menor que :value.',
        'string' => '📝 :attribute debe tener menos de :value caracteres.',
    ],
    'lte' => [
        'array' => '📋 :attribute no debe tener más de :value elementos.',
        'file' => '📁 :attribute debe ser menor o igual que :value kilobytes.',
        'numeric' => '🔢 :attribute debe ser menor o igual que :value.',
        'string' => '📝 :attribute no debe tener más de :value caracteres.',
    ],
    'mac_address' => '🌐 :attribute debe ser una dirección MAC válida.',
    'max' => [
        'array' => '📋 :attribute no debe tener más de :max elementos.',
        'file' => '📁 :attribute no debe ser mayor que :max kilobytes.',
        'numeric' => '🔢 :attribute no debe ser mayor que :max.',
        'string' => '📝 :attribute no debe tener más de :max caracteres.',
    ],
    'max_digits' => '🔢 :attribute no debe tener más de :max dígitos.',
    'mimes' => '📁 :attribute debe ser un archivo de tipo: :values.',
    'mimetypes' => '📁 :attribute debe ser un archivo de tipo: :values.',
    'min' => [
        'array' => '📋 :attribute debe tener al menos :min elementos.',
        'file' => '📁 :attribute debe ser de al menos :min kilobytes.',
        'numeric' => '🔢 :attribute debe ser de al menos :min.',
        'string' => '📝 :attribute debe tener al menos :min caracteres.',
    ],
    'min_digits' => '🔢 :attribute debe tener al menos :min dígitos.',
    'missing' => '📝 :attribute debe estar ausente.',
    'missing_if' => '📝 :attribute debe estar ausente cuando :other sea :value.',
    'missing_unless' => '📝 :attribute debe estar ausente a menos que :other sea :value.',
    'missing_with' => '📝 :attribute debe estar ausente cuando :values esté presente.',
    'missing_with_all' => '📝 :attribute debe estar ausente cuando :values estén presentes.',
    'multiple_of' => '🔢 :attribute debe ser un múltiplo de :value.',
    'not_in' => '🔘 El :attribute seleccionado es inválido.',
    'not_regex' => '📝 El formato de :attribute es inválido.',
    'numeric' => '🔢 :attribute debe ser un número.',
    'password' => [
        'letters' => '🔐 :attribute debe contener al menos una letra.',
        'mixed' => '🔐 :attribute debe contener al menos una letra mayúscula y una minúscula.',
        'numbers' => '🔐 :attribute debe contener al menos un número.',
        'symbols' => '🔐 :attribute debe contener al menos un símbolo.',
        'uncompromised' => '🔐 El :attribute dado ha aparecido en una filtración de datos. Por favor, elige un :attribute diferente.',
    ],
    'present' => '📝 :attribute debe estar presente.',
    'present_if' => '📝 :attribute debe estar presente cuando :other sea :value.',
    'present_unless' => '📝 :attribute debe estar presente a menos que :other sea :value.',
    'present_with' => '📝 :attribute debe estar presente cuando :values esté presente.',
    'present_with_all' => '📝 :attribute debe estar presente cuando :values estén presentes.',
    'prohibited' => '🚫 :attribute está prohibido.',
    'prohibited_if' => '🚫 :attribute está prohibido cuando :other sea :value.',
    'prohibited_unless' => '🚫 :attribute está prohibido a menos que :other esté en :values.',
    'prohibits' => '🚫 :attribute prohíbe que :other esté presente.',
    'regex' => '📝 El formato de :attribute es inválido.',
    'required' => '📝 :attribute es obligatorio.',
    'required_array_keys' => '📋 :attribute debe contener entradas para: :values.',
    'required_if' => '📝 :attribute es obligatorio cuando :other sea :value.',
    'required_if_accepted' => '📝 :attribute es obligatorio cuando :other sea aceptado.',
    'required_if_declined' => '📝 :attribute es obligatorio cuando :other sea rechazado.',
    'required_unless' => '📝 :attribute es obligatorio a menos que :other esté en :values.',
    'required_with' => '📝 :attribute es obligatorio cuando :values esté presente.',
    'required_with_all' => '📝 :attribute es obligatorio cuando :values estén presentes.',
    'required_without' => '📝 :attribute es obligatorio cuando :values no esté presente.',
    'required_without_all' => '📝 :attribute es obligatorio cuando ninguno de :values esté presente.',
    'same' => '🔄 :attribute y :other deben coincidir.',
    'size' => [
        'array' => '📋 :attribute debe contener :size elementos.',
        'file' => '📁 :attribute debe ser de :size kilobytes.',
        'numeric' => '🔢 :attribute debe ser :size.',
        'string' => '📝 :attribute debe tener :size caracteres.',
    ],
    'starts_with' => '📝 :attribute debe comenzar con uno de los siguientes: :values.',
    'string' => '📝 :attribute debe ser una cadena de texto.',
    'timezone' => '🌍 :attribute debe ser una zona horaria válida.',
    'unique' => '🔄 :attribute ya está en uso.',
    'uploaded' => '📁 :attribute falló al subir.',
    'uppercase' => '🔤 :attribute debe estar en mayúsculas.',
    'url' => '🌐 :attribute debe ser una URL válida.',
    'ulid' => '🔢 :attribute debe ser un ULID válido.',
    'uuid' => '🔢 :attribute debe ser un UUID válido.',

    /*
    |--------------------------------------------------------------------------
    | Mensajes de Validación Personalizados para Proveedores
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'business_name' => [
            'required' => '🏢 Por favor, escribe el nombre de la empresa o negocio. Este campo es obligatorio.',
            'max' => '🏢 El nombre de la empresa es muy largo. Máximo :max caracteres.',
            'unique' => '🏢 ¡Ups! Ya tienes registrada una empresa con ese nombre. Cambia el nombre o verifica si ya existe en tu lista de proveedores.',
        ],
        'tax_id' => [
            'required' => '📄 Por favor, escribe el RUC de la empresa. Este campo es obligatorio.',
            'max' => '📄 El RUC es muy largo. Máximo :max caracteres.',
            'unique' => '📄 ¡Cuidado! Ese RUC ya está registrado en otro proveedor. Revisa el número o busca el proveedor existente.',
            'digits' => '📄 El RUC debe tener exactamente :digits números. Ejemplo: 20123456789',
        ],
        'email' => [
            'email' => '📧 El correo electrónico no es válido. Ejemplo: empresa@gmail.com',
            'unique' => '📧 ¡Atención! Ese email ya lo usa otro proveedor. Usa un email diferente o verifica si ya tienes ese proveedor registrado.',
        ],
        'phone' => [
            'required' => '📞 Por favor, escribe el teléfono de contacto. Este campo es obligatorio.',
            'digits_between' => '📞 El teléfono debe tener entre :min y :max números. Ejemplo: 987654321',
        ],
        'address' => [
            'required' => '🏠 Por favor, escribe la dirección de la empresa. Este campo es obligatorio.',
            'max' => '🏠 La dirección es muy larga. Máximo :max caracteres.',
        ],
        'contact_name' => [
            'max' => '👤 El nombre del contacto es muy largo. Máximo :max caracteres.',
        ],
        'contact_phone' => [
            'digits_between' => '📞 El teléfono del contacto debe tener entre :min y :max números. Ejemplo: 987654321',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Nombres de Atributos Personalizados
    |--------------------------------------------------------------------------
    */

    'attributes' => [
        'business_name' => 'nombre de la empresa',
        'tax_id' => 'RUC',
        'email' => 'correo electrónico',
        'phone' => 'teléfono',
        'address' => 'dirección',
        'contact_name' => 'nombre del contacto',
        'contact_phone' => 'teléfono del contacto',
        'active' => 'estado activo',
    ],

];