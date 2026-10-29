<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Calificación académica
    |--------------------------------------------------------------------------
    |
    | Escala de 0 a 100. La nota final es el promedio simple de los dos
    | parciales y se aprueba con la nota mínima configurada aquí.
    |
    */
    'nota_minima_aprobacion' => (float) env('ACADEMICA_NOTA_MINIMA', 56),
    'nota_maxima' => 100.0,
];
