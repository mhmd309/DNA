<?php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');

require_once __DIR__ . '/app/bootstrap.php';

use App\Core\Auth;
use App\Core\Router;

Auth::init();

$router = new Router();

// Auth routes
$router->get('login', 'AuthController', 'showLogin');
$router->post('login', 'AuthController', 'login');
$router->get('logout', 'AuthController', 'logout');

// Dashboard
$router->get('', 'DashboardController', 'index', 'dashboard.view');
$router->get('dashboard', 'DashboardController', 'index', 'dashboard.view');

// Families
$router->get('families', 'FamilyController', 'index', 'families.view');
$router->get('families/create', 'FamilyController', 'create', 'families.create');
$router->post('families/store', 'FamilyController', 'store', 'families.create');
$router->get('families/show/{id}', 'FamilyController', 'show', 'families.view');
$router->get('families/edit/{id}', 'FamilyController', 'edit', 'families.edit');
$router->post('families/update/{id}', 'FamilyController', 'update', 'families.edit');
$router->post('families/delete/{id}', 'FamilyController', 'delete', 'families.delete');
$router->get('families/tree/{id}', 'FamilyController', 'tree', 'families.view');
$router->get('api/families/search', 'FamilyController', 'searchApi', 'families.view');

// Individuals
$router->get('individuals', 'IndividualController', 'index', 'individuals.view');
$router->get('individuals/create', 'IndividualController', 'create', 'individuals.create');
$router->post('individuals/store', 'IndividualController', 'store', 'individuals.create');
$router->get('individuals/show/{id}', 'IndividualController', 'show', 'individuals.view');
$router->get('individuals/edit/{id}', 'IndividualController', 'edit', 'individuals.edit');
$router->post('individuals/update/{id}', 'IndividualController', 'update', 'individuals.edit');
$router->post('individuals/delete/{id}', 'IndividualController', 'delete', 'individuals.delete');

// DNA Tests
$router->get('dna-tests', 'DnaTestController', 'index', 'dna.view');
$router->get('dna-tests/create', 'DnaTestController', 'create', 'dna.create');
$router->post('dna-tests/store', 'DnaTestController', 'store', 'dna.create');
$router->get('dna-tests/show/{id}', 'DnaTestController', 'show', 'dna.view');
$router->get('dna-tests/edit/{id}', 'DnaTestController', 'edit', 'dna.edit');
$router->post('dna-tests/update/{id}', 'DnaTestController', 'update', 'dna.edit');
$router->post('dna-tests/delete/{id}', 'DnaTestController', 'delete', 'dna.delete');

// Users
$router->get('users', 'UserController', 'index', 'users.view');
$router->get('users/create', 'UserController', 'create', 'users.create');
$router->post('users/store', 'UserController', 'store', 'users.create');
$router->get('users/edit/{id}', 'UserController', 'edit', 'users.edit');
$router->post('users/update/{id}', 'UserController', 'update', 'users.edit');
$router->post('users/delete/{id}', 'UserController', 'delete', 'users.delete');

// Global Search API
$router->get('api/search', 'SearchController', 'global', 'dashboard.view');

// Notifications API
$router->get('api/notifications', 'DashboardController', 'notifications', 'dashboard.view');

$url = $_GET['url'] ?? '';
$router->dispatch($url);
