:- dynamic signos_vitales/5.        % Para los signos vitales de los pacientes (ID, Nombre, Frecuencia, Oxigenación, Temperatura)
:- dynamic estadopaciente/3.        % Para el estado del paciente (ID, Nombre, Estado)
:- dynamic enfermode/3.             % Para los problemas del paciente (ID, Nombre)
:- dynamic rango_ritmo_cardiaco/5.  % Para los rangos de ritmo cardíaco (Estado, Min, Max)
:- dynamic rango_oximetria/1.       % Para los rangos de oxigenación (Valor mínimo)
:- dynamic rango_temperatura/2.     % Para los rangos de temperatura (Min, Max)
:- dynamic sesion_iniciada/2.


% Hechos de pacientes con signos vitales (ID, Nombre, Frecuencia, Oxigenación, Temperatura)
% Si cambia cada vez que se actualiza la bd
signos_vitales(133, 'Israel', 85, 97, 37.4).

% Hechos de pacientes y su estado (ID, nombre, estado)
estadopaciente(133, 'Israel', dormido).

% Hechos de pacientes enfermos (ID, Nombre, Problema)
enfermode(133, 'Israel', problema_cardiaco).
enfermode(133, 'Israel', arritmia).

% Hechos de rangos de ritmo cardíaco para diferentes estados
rango_ritmo_cardiaco(133, 'Israel', ejercicio, 100, 150).
rango_ritmo_cardiaco(133, 'Israel', reposo, 60, 90).
rango_ritmo_cardiaco(133, 'Israel', normal, 70, 100).
rango_ritmo_cardiaco(133, 'Israel', dormido, 50, 70).

% Valor mínimo de oximetría para evitar riesgo
rango_oximetria(93).

% Temperatura normal (minTemp, maxTemp)
rango_temperatura(36.5, 37.5).

% Regla 1: Frecuencia cardíaca fuera del rango para el estado actual
riesgo_frecuencia(Id, Nombre) :-
    estadopaciente(Id, Nombre, Estado),
    signos_vitales(Id, Nombre, Frecuencia, _, _),
    rango_ritmo_cardiaco(Id, Nombre, Estado, MinFreq, MaxFreq),
    (Frecuencia < MinFreq ; Frecuencia > MaxFreq).

% Regla 2: Oxigenación fuera del rango saludable
riesgo_oxigenacion(Id, Nombre) :-
    signos_vitales(Id, Nombre, _, Oxigenacion, _),
    rango_oximetria(MinOx),
    Oxigenacion < MinOx.

% Regla 3: Temperatura fuera del rango saludable
riesgo_temperatura(Id, Nombre) :-
    signos_vitales(Id, Nombre, _, _, Temperatura),
    rango_temperatura(MinTemp, MaxTemp),
    (Temperatura < MinTemp ; Temperatura > MaxTemp).

% Regla 4: Riesgo por múltiples signos fuera del rango
riesgo_multiples_signos(Id, Nombre) :-
    riesgo_frecuencia(Id, Nombre),
    riesgo_oxigenacion(Id, Nombre).

% Regla 5: Alerta combinada para baja oxigenación y baja frecuencia en reposo o dormido
alerta_sueno_baja_frecuencia(Id, Nombre) :-
    estadopaciente(Id, Nombre, Estado),
    signos_vitales(Id, Nombre, Frecuencia, Oxigenacion, _),
    (Estado = dormido ; Estado = reposo),
    rango_ritmo_cardiaco(Id, Nombre, Estado, MinFreq, _),
    rango_oximetria(MinOx),
    Frecuencia < MinFreq,
    Oxigenacion < MinOx.

% Regla 6: Alerta de fiebre al hacer ejercicio
alerta_fiebre_ejercicio(Id, Nombre) :-
    estadopaciente(Id, Nombre, ejercicio),
    signos_vitales(Id, Nombre, _, _, Temperatura),
    rango_temperatura(MinTemp, MaxTemp),
    Temperatura > MaxTemp.

% Regla 7: Alerta de desaturación (oxigenación baja) al hacer ejercicio
alerta_desaturacion_ejercicio(Id, Nombre) :-
    estadopaciente(Id, Nombre, ejercicio),
    signos_vitales(Id, Nombre, _, Oxigenacion, _),
    rango_oximetria(MinOx),
    Oxigenacion < MinOx.

% Regla 8: Alerta general por signos vitales fuera de rango en estado normal
alerta_general(Id, Nombre) :-
    estadopaciente(Id, Nombre, _),
    (riesgo_frecuencia(Id, Nombre) ;
     riesgo_oxigenacion(Id, Nombre) ;
     riesgo_temperatura(Id, Nombre)).

% Regla 9: Para saber si su oximetría es saludable o está en riesgo
oximetria_saludable(Id, Nombre) :-
    signos_vitales(Id, Nombre, _, Oximetria, _),
    rango_oximetria(MinOx),
    Oximetria >= MinOx.

% Regla 10: Alerta general si algún signo vital está fuera de los límites
alerta(Id, Nombre) :-
    riesgo_frecuencia(Id, Nombre);
    riesgo_oxigenacion(Id, Nombre);
    riesgo_temperatura(Id, Nombre).

% Regla para comprobar alarmas activadas
comprobar_alarmas(Id, Nombre, Frecuencia, Oxigenacion, Temperatura, AlarmasActivadas) :-
    % Actualiza temporalmente los signos vitales con los valores proporcionados
    (   
        retractall(signos_vitales(Id, Nombre, _, _, _)), % Borra cualquier signo vital previo para el paciente
        assertz(signos_vitales(Id, Nombre, Frecuencia, Oxigenacion, Temperatura))  % Inserta los nuevos valores
    ),

    % Verifica cada regla y acumula las alarmas activadas
    findall(Alarma, (
        (
            riesgo_frecuencia(Id, Nombre),
            writeln('Riesgo por frecuencia cardíaca detectado.'),
            Alarma = 'Riesgo por frecuencia cardíaca'
        );
        (
            riesgo_oxigenacion(Id, Nombre),
            writeln('Riesgo por oxigenación detectado.'),
            Alarma = 'Riesgo por oxigenación'
        );
        (
            riesgo_temperatura(Id, Nombre),
            writeln('Riesgo por temperatura detectado.'),
            Alarma = 'Riesgo por temperatura'
        );
        (
            riesgo_multiples_signos(Id, Nombre),
            writeln('Riesgo por múltiples signos fuera del rango detectado.'),
            Alarma = 'Riesgo por múltiples signos fuera del rango'
        );
        (
            alerta_sueno_baja_frecuencia(Id, Nombre),
            writeln('Alerta por baja oxigenación y frecuencia en reposo/dormido detectada.'),
            Alarma = 'Alerta por baja oxigenación y baja frecuencia en reposo/dormido'
        );
        (
            alerta_fiebre_ejercicio(Id, Nombre),
            writeln('Alerta por fiebre durante ejercicio detectada.'),
            Alarma = 'Alerta por fiebre durante ejercicio'
        );
        (
            alerta_desaturacion_ejercicio(Id, Nombre),
            writeln('Alerta por desaturación durante ejercicio detectada.'),
            Alarma = 'Alerta por desaturación durante ejercicio'
        )
    ), AlarmasActivadas),

    % Mensaje para depuración en caso de que no se activen alarmas
    (   
        AlarmasActivadas = [] ->
        writeln('No se activó ninguna alarma.')
        ;
        writeln('Evaluación completada con alarmas activadas.')
    ).


% Reglas para actualizar hechos
actualizar_signos_vitales(Id, Nombre, Frecuencia, Oxigenacion, Temperatura) :-
    (   
        signos_vitales(Id, Nombre, _, _, _) ->  % Verifica si ya existe
        retractall(signos_vitales(Id, Nombre, _, _, _)) ; true  % Elimina si existe
    ),
    assert(signos_vitales(Id, Nombre, Frecuencia, Oxigenacion, Temperatura)),  % Inserta el nuevo hecho
    guardar_cambios,
    writeln('Signos vitales actualizados correctamente.').

actualizar_estado_paciente(Id, Nombre, Estado) :-
    (   
        estadopaciente(Id, Nombre, _) ->  % Verifica si ya existe
        retractall(estadopaciente(Id, Nombre, _)) ; true  % Elimina si existe
    ),
    assert(estadopaciente(Id, Nombre, Estado)),  % Inserta el nuevo estado
    guardar_cambios,
    writeln('Estado del paciente actualizado correctamente.').

actualizar_rango_ritmo_cardiaco(Id, Nombre, Estado, MinFreq, MaxFreq) :-
    (   
        rango_ritmo_cardiaco(Id, Nombre, Estado, _, _) ->  % Verifica si ya existe
        retractall(rango_ritmo_cardiaco(Id, Nombre, Estado, _, _)) ; true  % Elimina si existe
    ),
    assert(rango_ritmo_cardiaco(Id, Nombre, Estado, MinFreq, MaxFreq)),  % Inserta el nuevo rango
    guardar_cambios,
    writeln('Rango de ritmo cardiaco actualizado correctamente.').


agregar_enfermedad_paciente(Id, Nombre, Problema) :-
    (   
        enfermode(Id, Nombre, Problema) ->  % Verifica si ya existe
        writeln('La enfermedad ya existe y será actualizada.') ; true  % Si existe, avisa
    ),
    retractall(enfermode(Id, Nombre, Problema)),  % Elimina la enfermedad específica si está repetida
    assert(enfermode(Id, Nombre, Problema)),  % Inserta la nueva enfermedad
    guardar_cambios,
    writeln('Enfermedad del paciente agregada o actualizada correctamente.').

    
borrar_enfermedad_paciente(Id, Nombre, Problema) :-
    (   
        enfermode(Id, Nombre, Problema) ->  % Comprueba si existe el hecho
        (
            retract(enfermode(Id, Nombre, Problema)),  % Si existe, lo elimina
            guardar_cambios,
            writeln('Enfermedad del paciente borrada correctamente.')
        ) ;
        (
            writeln('Error: La enfermedad especificada no existe.')
        )
    ).

% Consultar signos vitales con Id y Nombre, devolviendo Frecuencia, Oxigenacion y Temperatura
obtener_signos_vitales(Id, Nombre, Frecuencia, Oxigenacion, Temperatura) :-
    (   
        signos_vitales(Id, Nombre, Frecuencia, Oxigenacion, Temperatura) ->  % Verifica si el hecho existe
        format('frecuencia: ~w, oxigenacion: ~w, temperatura: ~w~n', [Frecuencia, Oxigenacion, Temperatura]) ;
        writeln('Error: No se encontraron signos vitales para el paciente.')
    ).

obtener_rangos_ritmo_cardiaco(Id, Nombre, Resultados) :-
    findall([Estado, MinFreq, MaxFreq], rango_ritmo_cardiaco(Id, Nombre, Estado, MinFreq, MaxFreq), Resultados),
    (   
        Resultados \= [] ->  % Verifica si la lista de resultados no está vacía
        writeln(Resultados) ;
        writeln('Error: No se encontraron rangos de ritmo cardíaco para el paciente.')
    ).

obtener_rango_oximetria(ValorMinimo) :-
    (   
        rango_oximetria(ValorMinimo) ->  % Verifica si el hecho existe
        format('minOx: ~w~n', [ValorMinimo]) ;
        writeln('Error: No se encontró rango de oximetría definido.')
    ).

obtener_rango_temperatura(MinTemp, MaxTemp) :-
    (   
        rango_temperatura(MinTemp, MaxTemp) ->  % Verifica si el hecho existe
        format('minTemp: ~w, maxTemp: ~w~n', [MinTemp, MaxTemp]) ;
        writeln('Error: No se encontró rango de temperatura definido.')
    ).

obtener_estado_paciente(Id, Nombre) :-
    (   
        estadopaciente(Id, Nombre, Estado) ->  % Verifica si existe el hecho con ID y Nombre
        format('estado: ~w~n', [Estado]) ;  % Si existe, imprime el estado
        writeln('Error: No se encontró estado para el paciente')  % Si no existe, muestra un error
    ).

obtener_enfermedades_paciente(Id, Nombre, Problemas) :-
    findall(Problema, enfermode(Id, Nombre, Problema), Problemas),
    (   
        Problemas \= [] ->  % Verifica si la lista de problemas no está vacía
        writeln(Problemas) ;
        writeln('Error: No se encontraron enfermedades para el paciente.')
    ).

% Regla para iniciar sesión (insertar el hecho sesion_iniciada/2)
iniciar_sesion(Id, Nombre) :-
    (   
        sesion_iniciada(Id, Nombre) ->  % Comprueba si ya hay una sesión activa para el mismo ID y Nombre
        format('id: ~w, nombre: ~w~n', [Id, Nombre])  % Mensaje informativo si la sesión ya está activa
        ;   % Si no hay sesión activa o es diferente
        (
            retractall(sesion_iniciada(_, _)), % Elimina todas las sesiones activas
            assertz(sesion_iniciada(Id, Nombre)), % Inserta el nuevo hecho
            guardar_cambios, % Guarda los cambios en el archivo
            format('id: ~w, nombre: ~w~n', [Id, Nombre]) 
        )
    ).

% Regla para cerrar sesión
cerrar_sesion :-
    (   
        sesion_iniciada(Id, Nombre) ->  % Verifica si hay una sesión activa
        (
            retractall(sesion_iniciada(_, _)), % Elimina todas las sesiones activas
            guardar_cambios, % Guarda los cambios
            format('Message: Sesión cerrada para ~w~n', [Nombre])
        ) ;
        writeln('Error: No hay ninguna sesión activa para cerrar.')  % Maneja el caso sin sesiones
    ).

% Regla para verificar si hay una sesión activa
sesion_activa :-
    (   
        sesion_iniciada(Id, Nombre) ->  % Comprueba si hay una sesión activa
        format('id: ~w, nombre: ~w~n', [Id, Nombre]) ;  % Si hay una sesión activa, muestra el ID y Nombre
        (
            writeln('Error: No hay ninguna sesión activa.')  % Si no hay sesiones activas, muestra un error
        )
    ).


% Guardar cambios en signos_vitales
guardar_cambios :-
    tell('C:/wamp64/www/gabs/PROYECTOIA/Healthcare-Proyecto-IA/conocimiento.pl'),
    listing(signos_vitales/5),
    listing(estadopaciente/3),
    listing(enfermode/3),           
    listing(rango_ritmo_cardiaco/5),
    listing(rango_oximetria/1),  
    listing(rango_temperatura/2),
    listing(sesion_iniciada/2), 
    listing(riesgo_frecuencia(Id, Paciente)),
    listing(riesgo_oxigenacion(Id, Paciente)),
    listing(riesgo_temperatura(Id, Paciente)),
    listing(riesgo_multiples_signos(Id, Paciente)),
    listing(alerta_sueno_baja_frecuencia(Id, Paciente)),
    listing(alerta_fiebre_ejercicio(Id, Paciente)),
    listing(alerta_desaturacion_ejercicio(Id, Paciente)),
    listing(alerta_general(Id, Paciente)),
    listing(oximetria_saludable(Id, Paciente)),
    listing(alerta(Id, Paciente)),
    listing(comprobar_alarmas(Id, Nombre, Frecuencia, Oxigenacion, Temperatura, AlarmasActivadas)),
    listing(actualizar_signos_vitales(Id, Nombre, Frecuencia, Oxigenacion, Temperatura)),
    listing(actualizar_estado_paciente(Id, Nombre, Estado)),
    listing(actualizar_rango_ritmo_cardiaco(Id, Nombre, Estado, MinFreq, MaxFreq)),
    listing(agregar_enfermedad_paciente(Id, Nombre, Problema)),
    listing(borrar_enfermedad_paciente(Id, Nombre, Problema)),
    listing(obtener_signos_vitales(Id, Nombre, Frecuencia, Oxigenacion, Temperatura)),
    listing(obtener_rangos_ritmo_cardiaco(Id, Nombre, Resultados)),
    listing(obtener_rango_oximetria(ValorMinimo)),
    listing(obtener_rango_temperatura(MinTemp, MaxTemp)),
    listing(obtener_estado_paciente(Id, Nombre)),
    listing(obtener_enfermedades_paciente(Id, Nombre, Problemas)),
    listing(iniciar_sesion(Id, Nombre)),
    listing(cerrar_sesion),
    listing(sesion_activa),
    listing(guardar_cambios),
    told.