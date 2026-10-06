<?php
/**
 * Public entry point for installations served from the project directory.
 * The application itself lives in /frontend, so redirect rather than relying
 * on Apache directory listings or a manually typed frontend URL.
 */
header('Location: frontend/', true, 302);
exit;
