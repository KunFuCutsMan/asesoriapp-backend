# AsesoriAPP - Servidor

La aplicación para regular asesorías en el Instituto Tecnológico de Veracruz

Este repositorio maneja el servidor utilizado para manejar el servicio

- [AsesoriAPP - Servidor](#asesoriapp---servidor)
  - [Acerca de](#acerca-de)
  - [Getting started](#getting-started)
    - [Prerequisitos](#prerequisitos)
    - [Instalación](#instalación)
  - [Uso](#uso)
    - [Pruebas](#pruebas)
  - [Contribución](#contribución)


## Acerca de

En el Instituto Tecnológico de Veracruz (ITV) el **Programa de Asesorías De Ingenierías Eléctrica, Electrónica y Renovables (PADIEER)** tiene el propósito de ayudar a los estudiantes de las carreras de ingenierías de las áreas Eléctrica-Electrónica en las materias que cursan en sus carreras, además de proveer ayuda a otros estudiantes pertenicientes de otras áreas.

Durante años se manejaban registros en papel, y a medida que se ha expandido el programa, la necesidad de reorganizar el manejo de información ha sido cada vez más aparente.

**AsesoriAPP** es una aplicación móvil que busca reducir el tiempo que un estudiante toma en pedir una asesoría del programa, designar un asesor apropiado para este, y coordinar el tiempo de ambos para encontrar el horario perfecto para la asesoría.

Este repositorio contiene el código responsable de manejar con las peticiones de los estudiantes y asesores, y conectarse con la base de datos responsable de los datos de las asesorías. El código de la aplicación móvil en sí se encuentra en [este repositorio](https://github.com/KunFuCutsMan/asesoriapp).

## Getting started

Este proyecto utiliza PHP y el framework Laravel para funcionar, por lo que la mayoría de la estructura de este repositorio sigue las prácticas descritas en la documentación de Laravel.

Puedes aprender más acerca del framework en [su documentación](https://laravel.com/docs/12.x).

### Prerequisitos

**[Instalar Laravel y PHP](https://laravel.com/docs/12.x/installation#creating-a-laravel-project):** Se recomienda seguir el tutorial establecido en la documentación de Laravel en caso que no tengas el interpretador de PHP y Composer.

Este proyecto utiliza las siguientes versiones:
* PHP **>=8.4**
* Laravel **12.x**

Si se desea correr el servidor localmente se requiere además de un servidor SQL, cuyas conexiones se pueden ver en el [archivo .env de ejemplo](./.env.example).

### Instalación

Para instalar las dependencias necesarias en el repositorio, solo necesitar correr este comando (asumiendo que tienes Composer):

```bash
composer install
```

¡No se te olvide copiar tus variables! Un ejemplo de las variables de entorno se puede encontrar en [.env.example](./.env.example).

## Uso

Puedes correr el servidor localmente mediante el comando

```bash
php artisan serve
```

Y el puerto del servidor se abrirá en `:8000`.

Hay que tener en cuenta que la única URL que será accesible es aquella dentro del camino `/api`.

Otros comandos se pueden encontrar mediante la línea de comandos Artisan, como parte del framework Laravel. Más información sobre que comandos existen se puede encontrar con el comando:

```bash
php artisan list
```

### Pruebas

Para facilitar el desarrollo del servidor sin tener que destruir tus bases de datos al hacerlo, es mejor utilizar la funcionalidad de pruebas de Laravel. Más información se puede encontrar en [su documentación](https://laravel.com/docs/12.x/testing). Este repositorio utiliza [PHPUnit](https://phpunit.de/).

```
php artisan test [./ruta/a/pruebas]
```

Las pruebas utilizan un servidor local de [SQLite3](https://sqlite.org/) para funcionar, ubicado en `database/database.sqlite`. Si no encuentras este archivo simplemente créalo antes de correr las pruebas.

## Contribución

Debido a que este es un proyecto interno para el Instituto Tecnológico de Veracruz, **solo se aceptarán contribuciones de gente perteneciente al instituto.** ¡Contacta a los administradores del repositorio si quieres contribuir a este proyecto! 
