<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * Copyright (c) 2014 Cees-Jan Kiewiet
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

namespace Cake\TwigView\Test\TestCase\Filesystem;

use Cake\Core\Configure;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use Cake\TwigView\Filesystem\TreeScanner;
use org\bovigo\vfs\vfsStream;

/**
 * Class TreeScannerTest.
 */
class TreeScannerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Router::reload();
        $this->loadPlugins(['TestTwigView']);
    }

    public function tearDown(): void
    {
        $this->removePlugins(['TestTwigView']);

        parent::tearDown();
    }

    public function testAll()
    {
        vfsStream::setup('root');
        $structure = [
            'templates' => [
                'layout' => [
                    'default.twig' => '',
                ],
                'element' => [
                    'element.twig' => '',
                ],
                'Blog' => [
                    'index.twig' => 'index',
                ],
            ],
        ];
        vfsStream::create($structure);

        $vfsPath = vfsStream::url('root');

        $templatePaths = Configure::read('App.paths.templates');
        Configure::delete('App.paths.templates');
        Configure::write('App.paths.templates', [$vfsPath . '/templates/']);

        $this->assertEquals([
            'APP' => [
                'Blog' => [
                    'index.twig',
                ],
                'element' => [
                    'element.twig',
                ],
                'layout' => [
                    'default.twig',
                ],
            ],
            'TestTwigView' => [
                4 => 'twig.twig',
                'Controller' => [
                    'Component' => [
                        'magic.twig',
                    ],
                    'index.twig',
                    'view.twig',
                ],
                'element' => [
                    'nested' => [
                        'plugin_test.twig',
                    ],
                ],
            ],
        ], TreeScanner::all(['.twig']));

        Configure::write('App.paths.templates', $templatePaths);
    }

    public function testPlugin()
    {
        $this->assertSame([
            4 => 'twig.twig',
            'Controller' => [
                'Component' => [
                    'magic.twig',
                ],
                'index.twig',
                'view.twig',
            ],
            'element' => [
                'nested' => [
                    'plugin_test.twig',
                ],
            ],
        ], TreeScanner::plugin('TestTwigView', ['.twig']));
    }
}
