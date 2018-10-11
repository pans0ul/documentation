<!DOCTYPE html>
<html lang="en">
<head>
<title>Documentation - Point Cloud Library (PCL)</title>
</head>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="X-UA-Compatible" content="IE=Edge" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Customizing the PCL build process &#8212; PCL 0.0 documentation</title>
    <link rel="stylesheet" href="_static/sphinxdoc.css" type="text/css" />
    <link rel="stylesheet" href="_static/pygments.css" type="text/css" />
    <script type="text/javascript" id="documentation_options" data-url_root="./" src="_static/documentation_options.js"></script>
    <script type="text/javascript" src="_static/jquery.js"></script>
    <script type="text/javascript" src="_static/underscore.js"></script>
    <script type="text/javascript" src="_static/doctools.js"></script>
    <script async="async" type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.1/MathJax.js?config=TeX-AMS-MML_HTMLorMML"></script>
    <link rel="search" title="Search" href="search.php" />
<?php
define('MODX_CORE_PATH', '/var/www/pointclouds.org/core/');
define('MODX_CONFIG_KEY', 'config');

require_once MODX_CORE_PATH.'config/'.MODX_CONFIG_KEY.'.inc.php';
require_once MODX_CORE_PATH.'model/modx/modx.class.php';
$modx = new modX();
$modx->initialize('web');

$snip = $modx->runSnippet("getSiteNavigation", array('id'=>5, 'phLevels'=>'sitenav.level0,sitenav.level1', 'showPageNav'=>'n'));
$chunkOutput = $modx->getChunk("site-header", array('sitenav'=>$snip));
$bodytag = str_replace("[[+showSubmenus:notempty=`", "", $chunkOutput);
$bodytag = str_replace("`]]", "", $bodytag);
echo $bodytag;
echo "\n";
?>
<div id="pagetitle">
<h1>Documentation</h1>
<a id="donate" href="http://www.openperception.org/support/"><img src="/assets/images/donate-button.png" alt="Donate to the Open Perception foundation"/></a>
</div>
<div id="page-content">

  </head><body>

    <div class="document">
      <div class="documentwrapper">
          <div class="body" role="main">
            
  <div class="section" id="customizing-the-pcl-build-process">
<span id="building-pcl"></span><h1>Customizing the PCL build process</h1>
<p>This tutorial explains how to modify the PCL cmake options and tweak your
building process to better fit the needs of your project and/or your system’s
requirements.</p>
</div>
<div class="section" id="audience">
<h1>Audience</h1>
<p>This tutorial targets users with a basic knowledge of CMake, C++ compilers,
linkers, flags and make.</p>
</div>
<div class="section" id="prerequisites">
<h1>Prerequisites</h1>
<p>We assume you have checked out the last available revision of PCL.</p>
</div>
<div class="section" id="pcl-basic-settings">
<h1>PCL basic settings</h1>
<p>Let’s say PCL is placed under /PATH/TO/PCL, which we will refer to as PCL_ROOT:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span>$ cd $PCL_ROOT
$ mkdir build
$ cmake ..
</pre></div>
</div>
<p>This will cause <cite>cmake</cite> to create a file called CMakeCache.txt in the build
directory with the default options.</p>
<p>Let’s have a look at what <cite>cmake</cite> options got enabled:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span>$ ccmake ..
</pre></div>
</div>
<p>You should see something like the following on screen:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span><span class="n">BUILD_common</span>                     <span class="n">ON</span>
<span class="n">BUILD_features</span>                   <span class="n">ON</span>
<span class="n">BUILD_filters</span>                    <span class="n">ON</span>
<span class="n">BUILD_global_tests</span>               <span class="n">OFF</span>
<span class="n">BUILD_io</span>                         <span class="n">ON</span>
<span class="n">BUILD_kdtree</span>                     <span class="n">ON</span>
<span class="n">BUILD_keypoints</span>                  <span class="n">ON</span>
<span class="n">BUILD_octree</span>                     <span class="n">ON</span>
<span class="n">BUILD_range_image</span>                <span class="n">ON</span>
<span class="n">BUILD_registration</span>               <span class="n">ON</span>
<span class="n">BUILD_sample_consensus</span>           <span class="n">ON</span>
<span class="n">BUILD_segmentation</span>               <span class="n">ON</span>
<span class="n">BUILD_surface</span>                    <span class="n">ON</span>
<span class="n">BUILD_visualization</span>              <span class="n">ON</span>
<span class="n">CMAKE_BUILD_TYPE</span>
<span class="n">CMAKE_INSTALL_PREFIX</span>             <span class="o">/</span><span class="n">usr</span><span class="o">/</span><span class="n">local</span>
<span class="n">PCL_SHARED_LIBS</span>                  <span class="n">ON</span>
<span class="n">PCL_VERSION</span>                      <span class="mf">1.0</span><span class="o">.</span><span class="mi">0</span>
<span class="n">VTK_DIR</span>                          <span class="o">/</span><span class="n">usr</span><span class="o">/</span><span class="n">local</span><span class="o">/</span><span class="n">lib</span><span class="o">/</span><span class="n">vtk</span><span class="o">-</span><span class="mf">5.6</span>
</pre></div>
</div>
</div>
<div class="section" id="the-explanation">
<h1>The explanation</h1>
<ul class="simple">
<li><cite>BUILD_common</cite>: option to enable/disable building of common library</li>
<li><cite>BUILD_features</cite>: option to enable/disable building of features library</li>
<li><cite>BUILD_filters</cite>: option to enable/disable building of filters library</li>
<li><cite>BUILD_global_tests</cite>: option to enable/disable building of global unit tests</li>
<li><cite>BUILD_io</cite>: option to enable/disable building of io library</li>
<li><cite>BUILD_kdtree</cite>: option to enable/disable building of kdtree library</li>
<li><cite>BUILD_keypoints</cite>: option to enable/disable building of keypoints library</li>
<li><cite>BUILD_octree</cite>: option to enable/disable building of octree library</li>
<li><cite>BUILD_range_image</cite>: option to enable/disable building of range_image library</li>
<li><cite>BUILD_registration</cite>: option to enable/disable building of registration library</li>
<li><cite>BUILD_sample_consensus</cite>: option to enable/disable building of sample_consensus library</li>
<li><cite>BUILD_segmentation</cite>: option to enable/disable building of segmentation library</li>
<li><cite>BUILD_surface</cite>: option to enable/disable building of surface library</li>
<li><cite>BUILD_visualization</cite>: option to enable/disable building of visualization library</li>
<li><cite>CMAKE_BUILD_TYPE</cite>: here you specify the build type. In CMake, a CMAKE_BUILD_TYPE corresponds to a set of options and flags passed to the compiler to activate/deactivate a functionality and to constrain the building process.</li>
<li><cite>CMAKE_INSTALL_PREFIX</cite>: where the headers and the built libraries will be installed</li>
<li><cite>PCL_SHARED_LIBS</cite>: option to enable building of shared libraries. Default is yes.</li>
<li><cite>PCL_VERSION</cite>: this is the PCL library version. It affects the built libraries names.</li>
<li><cite>VTK_DIR</cite>: directory of VTK library if found</li>
</ul>
<p>The above are called <cite>cmake</cite> cached variables. At this level we only looked at
the basic ones.</p>
</div>
<div class="section" id="tweaking-basic-settings">
<h1>Tweaking basic settings</h1>
<p>Depending on your project/system, you might want to enable/disable certain
options. For example, you can prevent the building of:</p>
<ul class="simple">
<li>tests: setting <cite>BUILD_global_tests</cite> to <cite>OFF</cite></li>
<li>a library: setting <cite>BUILD_LIBRARY_NAME</cite> to <cite>OFF</cite></li>
</ul>
<p>Note that if you disable a XXX library that is required for building
YYY then XXX will be built but won’t appear in the cache.</p>
<p>You can also change the build type:</p>
<ul class="simple">
<li><strong>Debug</strong>: means that no optimization is done and all the debugging symbols are embedded into the libraries file. This is platform and compiler dependent. On Linux with gcc this is equivalent to running gcc with <cite>-O0 -g -ggdb -Wall</cite></li>
<li><strong>Release</strong>: the compiled code is optimized and no debug information will be printed out. This will lead to <cite>-O3</cite> for gcc and <cite>-O5</cite> for clang</li>
<li><strong>RelWithDebInfo</strong>: the compiled code is optimized but debugging data is also embedded in the libraries. This is a tradeoff between the two former ones.</li>
<li><strong>MinSizeRel</strong>: this, normally, results in the smallest libraries you can build. This is interesting when building for Android or a restricted memory/space system.</li>
</ul>
<p>A list of available CMAKE_BUILD_TYPEs can be found typing:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span>$ cmake --help-variable CMAKE_BUILD_TYPE
</pre></div>
</div>
</div>
<div class="section" id="tweaking-advanced-settings">
<h1>Tweaking advanced settings</h1>
<p>Now we are done with all the basic stuff. To turn on advanced cache
options hit <cite>t</cite> while in ccmake.
Advanced options become especially useful when you have dependencies
installed in unusual locations and thus cmake hangs with
<cite>XXX_NOT_FOUND</cite> this can even prevent you from building PCL although
you have all the dependencies installed. In this section we will
discuss each dependency entry so that you can configure/build or
update/build PCL according to your system.</p>
<div class="section" id="building-unit-tests">
<h2>Building unit tests</h2>
<p>If you want to contribute to PCL, or are modifying the code, you need
to turn on building of unit tests. This is accomplished by setting the <cite>BUILD_global_tests</cite>
option to <cite>ON</cite>, with a few caveats. If you’re using <cite>ccmake</cite> and you find that <cite>BUILD_global_tests</cite>
is reverting to <cite>OFF</cite> when you configure, you can move the cursor up to the <cite>BUILD_global_tests</cite> line to see the
error message.</p>
<p>Two options which will need to be turned ON before <cite>BUILD_global_tests</cite> are <cite>BUILD_outofcore</cite> and
<cite>BUILD_people</cite>. Your mileage may vary.</p>
<p>Also required for unit tests is the source code for the Google C++ Testing Framework. That is
usually as simple as downloading the source, extracting it, and pointing the <cite>GTEST_SRC_DIR</cite> and <cite>GTEST_INCLUDE_DIR</cite>
options to the applicable source locations. On Ubuntu, you can simply run <cite>apt-get install libgtest-dev</cite>.</p>
<p>These steps enable the <cite>tests</cite> make target, so you can use <cite>make tests</cite> to run tests.</p>
</div>
<div class="section" id="general-remarks">
<h2>General remarks</h2>
<p>Under ${PCL_ROOT}/cmake/Modules there is a list of FindXXX.cmake files
used to locate dependencies and set their related variables. They have
a list of default searchable paths where to look for them. In addition,
if pkg-config is available then it is triggered to get hints on their
locations. If all of them fail, then we look for a CMake entry or
environment variable named <strong>XXX_ROOT</strong> to find headers and libraries.
We recommend setting an environment variable since it is independent
from CMake and lasts over the changes you can make to your
configuration.</p>
<p>The available ROOTs you can set are as follow:</p>
<ul class="simple">
<li><strong>BOOST_ROOT</strong>: for boost libraries with value <cite>C:/Program Files/boost-1.4.6</cite> for instance</li>
<li><strong>CMINPACK_ROOT</strong>: for cminpack with value <cite>C:/Program Files/CMINPACK 1.1.13</cite> for instance</li>
<li><strong>QHULL_ROOT</strong>: for qhull with value <cite>C:/Program Files/qhull 6.2.0.1373</cite> for instance</li>
<li><strong>FLANN_ROOT</strong>: for flann with value <cite>C:/Program Files/flann 1.6.8</cite> for instance</li>
<li><strong>EIGEN_ROOT</strong>: for eigen with value <cite>C:/Program Files/Eigen 3.0.0</cite> for instance</li>
</ul>
<p>To ensure that all the dependencies were correctly found, beside the
message you get from CMake, you can check or edit each dependency specific
variables and give it the value that best fits your needs.</p>
<p>UNIX users generally don’t have to bother with debug vs release versions
they are fully compliant. You would just loose debug symbols if you use
release libraries version instead of debug while you will end up with much
more verbose output and slower execution. This said, Windows MSVC users
and Apple iCode ones can build debug/release from the same project, thus
it will be safer and more coherent to fill them accordingly.</p>
</div>
<div class="section" id="detailed-description">
<h2>Detailed description</h2>
<p>Below, each dependency variable is listed, its meaning is explained
then a sample value is given for reference.</p>
<ul class="simple">
<li>Boost</li>
</ul>
<table border="1" class="docutils">
<colgroup>
<col width="24%" />
<col width="45%" />
<col width="30%" />
</colgroup>
<thead valign="bottom">
<tr class="row-odd"><th class="head">cache variable</th>
<th class="head">meaning</th>
<th class="head">sample value</th>
</tr>
</thead>
<tbody valign="top">
<tr class="row-even"><td>Boost_DATE_TIME_LIBRARY</td>
<td>full path to boost_date-time.[so,lib,a]</td>
<td>/usr/local/lib/libboost_date_time.so</td>
</tr>
<tr class="row-odd"><td>Boost_DATE_TIME_LIBRARY_DEBUG</td>
<td>full path to boost_date-time.[so,lib,a] (debug version)</td>
<td>/usr/local/lib/libboost_date_time-gd.so</td>
</tr>
<tr class="row-even"><td>Boost_DATE_TIME_LIBRARY_RELEASE</td>
<td>full path to boost_date-time.[so,lib,a] (release version)</td>
<td>/usr/local/lib/libboost_date_time.so</td>
</tr>
<tr class="row-odd"><td>Boost_FILESYSTEM_LIBRARY</td>
<td>full path to boost_filesystem.[so,lib,a]</td>
<td>/usr/local/lib/libboost_filesystem.so</td>
</tr>
<tr class="row-even"><td>Boost_FILESYSTEM_LIBRARY_DEBUG</td>
<td>full path to boost_filesystem.[so,lib,a] (debug version)</td>
<td>/usr/local/lib/libboost_filesystem-gd.so</td>
</tr>
<tr class="row-odd"><td>Boost_FILESYSTEM_LIBRARY_RELEASE</td>
<td>full path to boost_filesystem.[so,lib,a] (release version)</td>
<td>/usr/local/lib/libboost_filesystem.so</td>
</tr>
<tr class="row-even"><td>Boost_INCLUDE_DIR</td>
<td>path to boost headers directory</td>
<td>/usr/local/include</td>
</tr>
<tr class="row-odd"><td>Boost_LIBRARY_DIRS</td>
<td>path to boost libraries directory</td>
<td>/usr/local/lib</td>
</tr>
<tr class="row-even"><td>Boost_SYSTEM_LIBRARY</td>
<td>full path to boost_system.[so,lib,a]</td>
<td>/usr/local/lib/libboost_system.so</td>
</tr>
<tr class="row-odd"><td>Boost_SYSTEM_LIBRARY_DEBUG</td>
<td>full path to boost_system.[so,lib,a] (debug version)</td>
<td>/usr/local/lib/libboost_system-gd.so</td>
</tr>
<tr class="row-even"><td>Boost_SYSTEM_LIBRARY_RELEASE</td>
<td>full path to boost_system.[so,lib,a] (release version)</td>
<td>/usr/local/lib/libboost_system.so</td>
</tr>
<tr class="row-odd"><td>Boost_THREAD_LIBRARY</td>
<td>full path to boost_thread.[so,lib,a]</td>
<td>/usr/local/lib/libboost_thread.so</td>
</tr>
<tr class="row-even"><td>Boost_THREAD_LIBRARY_DEBUG</td>
<td>full path to boost_thread.[so,lib,a] (debug version)</td>
<td>/usr/local/lib/libboost_thread-gd.so</td>
</tr>
<tr class="row-odd"><td>Boost_THREAD_LIBRARY_RELEASE</td>
<td>full path to boost_thread.[so,lib,a] (release version)</td>
<td>/usr/local/lib/libboost_thread.so</td>
</tr>
</tbody>
</table>
<ul class="simple">
<li>CMinpack</li>
</ul>
<table border="1" class="docutils">
<colgroup>
<col width="21%" />
<col width="49%" />
<col width="30%" />
</colgroup>
<thead valign="bottom">
<tr class="row-odd"><th class="head">cache variable</th>
<th class="head">meaning</th>
<th class="head">sample value</th>
</tr>
</thead>
<tbody valign="top">
<tr class="row-even"><td>CMINPACK_INCLUDE_DIR</td>
<td>path to cminpack headers directory</td>
<td>/usr/local/include/cminpack-1</td>
</tr>
<tr class="row-odd"><td>CMINPACK_LIBRARY</td>
<td>full path to cminpack.[so,lib,a] (release version)</td>
<td>/usr/local/lib/libcminpack.so</td>
</tr>
<tr class="row-even"><td>CMINPACK_LIBRARY_DEBUG</td>
<td>full path to cminpack.[so,lib,a] (debug version)</td>
<td>/usr/local/lib/libcminpack-gd.so</td>
</tr>
</tbody>
</table>
<ul class="simple">
<li>FLANN</li>
</ul>
<table border="1" class="docutils">
<colgroup>
<col width="18%" />
<col width="52%" />
<col width="30%" />
</colgroup>
<thead valign="bottom">
<tr class="row-odd"><th class="head">cache variable</th>
<th class="head">meaning</th>
<th class="head">sample value</th>
</tr>
</thead>
<tbody valign="top">
<tr class="row-even"><td>FLANN_INCLUDE_DIR</td>
<td>path to flann headers directory</td>
<td>/usr/local/include</td>
</tr>
<tr class="row-odd"><td>FLANN_LIBRARY</td>
<td>full path to libflann_cpp.[so,lib,a] (release version)</td>
<td>/usr/local/lib/libflann_cpp.so</td>
</tr>
<tr class="row-even"><td>FLANN_LIBRARY_DEBUG</td>
<td>full path to libflann_cpp.[so,lib,a] (debug version)</td>
<td>/usr/local/lib/libflann_cpp-gd.so</td>
</tr>
</tbody>
</table>
<ul class="simple">
<li>Eigen</li>
</ul>
<table border="1" class="docutils">
<colgroup>
<col width="23%" />
<col width="42%" />
<col width="35%" />
</colgroup>
<thead valign="bottom">
<tr class="row-odd"><th class="head">cache variable</th>
<th class="head">meaning</th>
<th class="head">sample value</th>
</tr>
</thead>
<tbody valign="top">
<tr class="row-even"><td>EIGEN_INCLUDE_DIR</td>
<td>path to eigen headers directory</td>
<td>/usr/local/include/eigen3</td>
</tr>
</tbody>
</table>
</div>
</div>


          </div>
      </div>
      <div class="clearer"></div>
    </div>
</div> <!-- #page-content -->

<?php
$chunkOutput = $modx->getChunk("site-footer");
echo $chunkOutput;
?>

  </body>
</html>