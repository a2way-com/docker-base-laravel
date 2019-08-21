<?php

echo '<h1 style="text-align:center;">A2Way Docker Base Laravel</h1>';

echo '<p style="text-align:center;"><a href="https://a2way.com">https://a2way.com</a></p>';

echo '<p style="text-align:center;">
This Docker Image contains following:
<ul>
<li>Alpine Linux base.</li>
<li>Nginx.</li>
<li>PHP-FPM.</li>
<li>Supervisor to keep Nginx and PHP-FPM running.</li>
<li>Composer.</li>
<li>A script to auto build the <code>.env</code> file based on environment variables provided into the container.</li>
</ul>
</p>';

echo '<p>To use, either <code>COPY</code> or mount Laravel root directory into <code>/app</code> directory. You can use Composer to bootstrap a Laravel project right inside the Docker container\'s <code>/app</code> directory. By having that directory mounted to the host file system, you can persist the Laravel files.</p>';

echo '<p>To build the <code>.env</code> file, inject an environment variable named <code>LARAVEL_VARS</code>, and list all Laravel environment variable names as <code>LARAVEL_VARS</code>\'s value. Make the Laravel environment variable names space-separated (Eg: <code>LARAVEL_VARS=APP_NAME APP_ENV APP_KEY APP_DEBUG ...</code>.). Then inject each of those environment variables with their values.</p>';
