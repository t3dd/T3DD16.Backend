<?php
namespace T3DD\Build;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

if (getenv('TYPO3_PATH_WEB')) {
    putenv('TYPO3_PATH_WEB='. realpath(__DIR__ . '/' . ltrim(getenv('TYPO3_PATH_WEB'), '/')));
}

require dirname(__DIR__) . '/Web/typo3/sysext/core/Build/UnitTestsBootstrap.php';