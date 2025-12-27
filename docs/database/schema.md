# Descripción de Estado

En el _ITV_ existen varias 13 **carreras** de alguna ingeniería o licenciatura, de las cuales en sus programas poseen diferentes **asignaturas**, acorde a lo establecido por el Sistema Nacional de Tecnológicos de México. Algunas **asignaturas** son únicas de alguna **carrera**, mientras otras son compartidas, o se consideran de tronco común; es necesario que existan en el programa de todas las **carreras**. Las **carreras** tienen ciertas **especialidades** como extensión de sus programas de estudio.

Un **estudiante** estudia alguna de las **carreras** y debe seguir su programa de estudios, por lo que puede que necesite ayuda en alguna de estas **asignaturas**. Un **asesor** es un **estudiante** del _ITV_ que forma parte de _PADIEER_, y puede enseñar acerca de **asignaturas** pertenecientes a su **carrera**.

Una **asesoría** es un tiempo de enseñanza impartido por un **asesor** acerca de alguna **asignatura**, hacia uno o más **estudiantes** en un **horario** establecido previamente. Los **horarios** pueden ser desde 8am hasta 8pm, de lunes a viernes. El **estudiante** pide la **asesoría**, y debe esperar a ser notificado para saber quien será su **asesor.**

Un **admin** o **administrador** es un **asesor** encargado del programa de asesorías, y puede designar un **asesor** a **asesorías** pedidas por **estudiantes**. El **admin** escoge a un **asesor** apropiado en base primero de sus **horarios** y si puede enseñar la **asignatura** solicitada, y en caso de no encontrar un asesor con tales cualidades, uno que pueda enseñar dicha asignatura, y si no hay otro, que pertenezca a la misma **carrera** del **estudiante** que solicitó la **asesoría**. Si no hay asesores disponibles, entonces puede escoger a cualquiera, o cancelar la **asesoría**.

Una **asesoría** inicialmente está en **estado _pendiente_**; sin importar que tenga un **asesor** asignado; en **estado _en proceso_**, cuando el **asesor** indique que empezó la asesoría en el la fecha y hora establecida, en **estado _terminado_** una vez que se confirme que terminó la asesoría, ó en **estado _cancelado_** en cualquier momento de la existencia de la asesoría.

Una **asesoría** puede ser cancelada por las siguientes razones:
- Un administrador cancela la asesoría
- El estudiante que pidió la asesoría la cancela
- El asesor asignado cancela la asesoría

Para confirmar que se terminó con éxito la **asesoría**, el **asesor** debe compartir un código al **estudiante**, y una vez confirmado el código, el estudiante termina la asesoría. Sólo se puede terminar una asesoría que esté **_en proceso_**.

Las **asesorías** terminadas con éxito se consideran como una hora de servicio para el **asesor** que la impartió, y con 200 horas de servicio libera su servicio social de su programa de estudios. Si un **asesor** asesora a varios **estudiantes** en una misma hora, entonces recibe una hora de servicio por cada **estudiante**.

Un **administrador** puede añadir o restar horas de servicio a un **asesor**.

## Diagrama de Estado

```mermaid
erDiagram
    CARRERA {
        int id PK
        string nombre
        string codigo
    }
    ASIGNATURA {
        int id PK
        string nombre
    }
    ESPECIALIDAD {
        int id PK
        string nombre
        int carreraID FK
    }

    ESTUDIANTE {
        int id PK
        string(8) numeroControl
        string(60) contrasena "hidden"
        string nombre
        string apellidoPaterno
        string apellidoMaterno
        string(10) numeroTelefono
        int semestre
        int carreraID FK
        int especialidadID FK "opcional"
    }
    ASESOR {
        int id PK
        int estudianteID FK
    }
    ADMIN {
        int id PK
        int asesorID FK
    }

    HORARIO {
        int id PK
        time horaInicio
        time horaFinal
        bool disponible
        int diaSemanaID FK
        int asesorID FK
    }
    DIASEMANA {
        int id PK
        string nombre
    }

    ASESORIA {
        int id PK
        date diaAsesoria
        time horaInicial
        time horaFinal
        string codigoSeguridad
        int estudianteID FK
        int asesorID FK "nullable"
        int carreraID FK
        int asignaturaID FK
        int estadoAsesoriaID FK
    }

    ESTADOASESORIA {
        int id PK
        string nombre
    }

    CARRERA ||--|{ ESPECIALIDAD : tiene
    ASIGNATURA ||..|{ CARRERA : pertenece

    ESTUDIANTE ||--|| CARRERA : cursa
    ESTUDIANTE ||..|| ESPECIALIDAD : "se especializa"

    ESTUDIANTE ||--o| ASESOR : "puede ser"
    ASESOR ||--o| ADMIN : "puede ser"

    ASESOR }o..o{ ASIGNATURA : imparte
    ASESOR }o..o{ HORARIO : "enseña en"
    HORARIO }o--|| DIASEMANA : ocurre

    ASESORIA }|..|| ESTADOASESORIA : "con estado"
    ASESOR ||..o{ ASESORIA : asesora
    ASESORIA }|--|| ASIGNATURA : "es de"
    ESTUDIANTE ||..o{ ASESORIA : pide
    ASESORIA }|--|| CARRERA : "es de"
```
