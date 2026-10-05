<?php
/**
 * Workspace entry point.
 * This lets the folder opened in VS Code run directly from the XAMPP htdocs
 * directory while the application source remains in Major-project.
 */
header('Location: Major-project/', true, 302);
exit;
