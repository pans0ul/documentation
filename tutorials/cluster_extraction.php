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
    <title>Euclidean Cluster Extraction &#8212; PCL 0.0 documentation</title>
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
            
  <div class="section" id="euclidean-cluster-extraction">
<span id="cluster-extraction"></span><h1>Euclidean Cluster Extraction</h1>
<p>In this tutorial we will learn how to <strong>extract Euclidean clusters</strong> with the
<code class="docutils literal notranslate"><span class="pre">pcl::EuclideanClusterExtraction</span></code> class. In order to not complicate the
tutorial, certain elements of it such as the plane segmentation algorithm,
will not be explained here. Please check the <a class="reference internal" href="planar_segmentation.php#planar-segmentation"><span class="std std-ref">Plane model segmentation</span></a>
tutorial for more information.</p>
</div>
<div class="section" id="theoretical-primer">
<h1>Theoretical Primer</h1>
<p>A clustering method needs to divide an unorganized point cloud model <img class="math" src="_images/math/9dcbbef8e0f76051d388013b90a95bec3069e484.png" alt="P"/>
into smaller parts so that the overall processing time for <img class="math" src="_images/math/9dcbbef8e0f76051d388013b90a95bec3069e484.png" alt="P"/> is
significantly reduced. A simple data clustering approach in an Euclidean sense
can be implemented by making use of a 3D grid subdivision of the space using
fixed width boxes, or more generally, an octree data structure. This particular
representation is very fast to build and is useful for situations where either
a volumetric representation of the occupied space is needed, or the data in
each resultant 3D box (or octree leaf) can be approximated with a different
structure. In a more general sense however, we can make use of nearest
neighbors and implement a clustering technique that is essentially similar to a
flood fill algorithm.</p>
<p>Let’s assume we have given a point cloud with a table and objects on top of it.
We want to find and segment the individual object point clusters lying on the
plane. Assuming that we use a Kd-tree structure for finding the nearest
neighbors, the algorithmic steps for that would be (from <a class="reference internal" href="how_features_work.php#rusudissertation" id="id1">[RusuDissertation]</a>):</p>
<blockquote>
<div><ol class="arabic">
<li><p class="first"><em>create a Kd-tree representation for the input point cloud dataset</em> <img class="math" src="_images/math/9dcbbef8e0f76051d388013b90a95bec3069e484.png" alt="P"/>;</p>
</li>
<li><p class="first"><em>set up an empty list of clusters</em> <img class="math" src="_images/math/afce44aa7c55836ca9345404c22fc7b599d2ed84.png" alt="C"/>, <em>and a queue of the points that need to be checked</em> <img class="math" src="_images/math/331157c6afbd113256867fd408d80277d1a24756.png" alt="Q"/>;</p>
</li>
<li><p class="first"><em>then for every point</em> <img class="math" src="_images/math/a0ad00746bf1f6eaa3ebe057155481058f5168e5.png" alt="\boldsymbol{p}_i \in P"/>, <em>perform the following steps:</em></p>
<blockquote>
<div><ul>
<li><p class="first"><em>add</em> <img class="math" src="_images/math/1cd445b90bca7b1b01b56133314dd58344781f38.png" alt="\boldsymbol{p}_i"/> <em>to the current queue</em> <img class="math" src="_images/math/331157c6afbd113256867fd408d80277d1a24756.png" alt="Q"/>;</p>
</li>
<li><p class="first"><em>for every point</em> <img class="math" src="_images/math/b8dca20fe1cbbb18fc093f8ef5dd47199d6dd925.png" alt="\boldsymbol{p}_i \in Q"/> <em>do:</em></p>
<blockquote>
<div><ul class="simple">
<li><em>search for the set</em> <img class="math" src="_images/math/ef4617cf3bae345d61012688b0f46f8c6e5dc01d.png" alt="P^i_k"/> <em>of point neighbors of</em> <img class="math" src="_images/math/1cd445b90bca7b1b01b56133314dd58344781f38.png" alt="\boldsymbol{p}_i"/> <em>in a sphere with radius</em> <img class="math" src="_images/math/6d7e1c410323c06e69e809b963cf2646c8436026.png" alt="r &lt; d_{th}"/>;</li>
<li><em>for every neighbor</em> <img class="math" src="_images/math/ddd39af254aa3eeed4c07982844474fc227ec7f2.png" alt="\boldsymbol{p}^k_i \in P^k_i"/>, <em>check if the point has already been processed, and if not add it to</em> <img class="math" src="_images/math/331157c6afbd113256867fd408d80277d1a24756.png" alt="Q"/>;</li>
</ul>
</div></blockquote>
</li>
</ul>
</div></blockquote>
<ul class="simple">
<li><em>when the list of all points in</em> <img class="math" src="_images/math/331157c6afbd113256867fd408d80277d1a24756.png" alt="Q"/> <em>has been processed, add</em>
<img class="math" src="_images/math/331157c6afbd113256867fd408d80277d1a24756.png" alt="Q"/> <em>to the list of clusters</em> <img class="math" src="_images/math/afce44aa7c55836ca9345404c22fc7b599d2ed84.png" alt="C"/>, <em>and reset</em> <img class="math" src="_images/math/331157c6afbd113256867fd408d80277d1a24756.png" alt="Q"/> <em>to an
empty list</em></li>
</ul>
</li>
<li><p class="first"><em>the algorithm terminates when all points</em> <img class="math" src="_images/math/a0ad00746bf1f6eaa3ebe057155481058f5168e5.png" alt="\boldsymbol{p}_i \in P"/> <em>have been processed and are now part of the list of point clusters</em> <img class="math" src="_images/math/afce44aa7c55836ca9345404c22fc7b599d2ed84.png" alt="C"/></p>
</li>
</ol>
</div></blockquote>
</div>
<div class="section" id="the-code">
<h1>The Code</h1>
<p>First, download the dataset <a class="reference external" href="https://raw.github.com/PointCloudLibrary/data/master/tutorials/table_scene_lms400.pcd">table_scene_lms400.pcd</a> and save it somewhere to disk.</p>
<p>Then, create a file, let’s say, <code class="docutils literal notranslate"><span class="pre">cluster_extraction.cpp</span></code> in your favorite
editor, and place the following inside it:</p>
<div class="highlight-cpp notranslate"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre>  1
  2
  3
  4
  5
  6
  7
  8
  9
 10
 11
 12
 13
 14
 15
 16
 17
 18
 19
 20
 21
 22
 23
 24
 25
 26
 27
 28
 29
 30
 31
 32
 33
 34
 35
 36
 37
 38
 39
 40
 41
 42
 43
 44
 45
 46
 47
 48
 49
 50
 51
 52
 53
 54
 55
 56
 57
 58
 59
 60
 61
 62
 63
 64
 65
 66
 67
 68
 69
 70
 71
 72
 73
 74
 75
 76
 77
 78
 79
 80
 81
 82
 83
 84
 85
 86
 87
 88
 89
 90
 91
 92
 93
 94
 95
 96
 97
 98
 99
100
101
102</pre></div></td><td class="code"><div class="highlight"><pre><span></span><span class="cp">#include</span> <span class="cpf">&lt;pcl/ModelCoefficients.h&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/point_types.h&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/io/pcd_io.h&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/filters/extract_indices.h&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/filters/voxel_grid.h&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/features/normal_3d.h&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/kdtree/kdtree.h&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/sample_consensus/method_types.h&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/sample_consensus/model_types.h&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/segmentation/sac_segmentation.h&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/segmentation/extract_clusters.h&gt;</span><span class="cp"></span>


<span class="kt">int</span> 
<span class="nf">main</span> <span class="p">(</span><span class="kt">int</span> <span class="n">argc</span><span class="p">,</span> <span class="kt">char</span><span class="o">**</span> <span class="n">argv</span><span class="p">)</span>
<span class="p">{</span>
  <span class="c1">// Read in the cloud data</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PCDReader</span> <span class="n">reader</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">),</span> <span class="n">cloud_f</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">reader</span><span class="p">.</span><span class="n">read</span> <span class="p">(</span><span class="s">&quot;table_scene_lms400.pcd&quot;</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;PointCloud before filtering has: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; data points.&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span> <span class="c1">//*</span>

  <span class="c1">// Create the filtering object: downsample the dataset using a leaf size of 1cm</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">VoxelGrid</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">vg</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud_filtered</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">vg</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>
  <span class="n">vg</span><span class="p">.</span><span class="n">setLeafSize</span> <span class="p">(</span><span class="mf">0.01f</span><span class="p">,</span> <span class="mf">0.01f</span><span class="p">,</span> <span class="mf">0.01f</span><span class="p">);</span>
  <span class="n">vg</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_filtered</span><span class="p">);</span>
  <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;PointCloud after filtering has: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud_filtered</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span>  <span class="o">&lt;&lt;</span> <span class="s">&quot; data points.&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span> <span class="c1">//*</span>

  <span class="c1">// Create the segmentation object for the planar model and set all the parameters</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">SACSegmentation</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">seg</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointIndices</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">inliers</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointIndices</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">ModelCoefficients</span><span class="o">::</span><span class="n">Ptr</span> <span class="n">coefficients</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">ModelCoefficients</span><span class="p">);</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud_plane</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">());</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PCDWriter</span> <span class="n">writer</span><span class="p">;</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setOptimizeCoefficients</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setModelType</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">SACMODEL_PLANE</span><span class="p">);</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setMethodType</span> <span class="p">(</span><span class="n">pcl</span><span class="o">::</span><span class="n">SAC_RANSAC</span><span class="p">);</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setMaxIterations</span> <span class="p">(</span><span class="mi">100</span><span class="p">);</span>
  <span class="n">seg</span><span class="p">.</span><span class="n">setDistanceThreshold</span> <span class="p">(</span><span class="mf">0.02</span><span class="p">);</span>

  <span class="kt">int</span> <span class="n">i</span><span class="o">=</span><span class="mi">0</span><span class="p">,</span> <span class="n">nr_points</span> <span class="o">=</span> <span class="p">(</span><span class="kt">int</span><span class="p">)</span> <span class="n">cloud_filtered</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span>
  <span class="k">while</span> <span class="p">(</span><span class="n">cloud_filtered</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span> <span class="o">&gt;</span> <span class="mf">0.3</span> <span class="o">*</span> <span class="n">nr_points</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="c1">// Segment the largest planar component from the remaining cloud</span>
    <span class="n">seg</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud_filtered</span><span class="p">);</span>
    <span class="n">seg</span><span class="p">.</span><span class="n">segment</span> <span class="p">(</span><span class="o">*</span><span class="n">inliers</span><span class="p">,</span> <span class="o">*</span><span class="n">coefficients</span><span class="p">);</span>
    <span class="k">if</span> <span class="p">(</span><span class="n">inliers</span><span class="o">-&gt;</span><span class="n">indices</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span> <span class="o">==</span> <span class="mi">0</span><span class="p">)</span>
    <span class="p">{</span>
      <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;Could not estimate a planar model for the given dataset.&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
      <span class="k">break</span><span class="p">;</span>
    <span class="p">}</span>

    <span class="c1">// Extract the planar inliers from the input cloud</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">ExtractIndices</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">extract</span><span class="p">;</span>
    <span class="n">extract</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud_filtered</span><span class="p">);</span>
    <span class="n">extract</span><span class="p">.</span><span class="n">setIndices</span> <span class="p">(</span><span class="n">inliers</span><span class="p">);</span>
    <span class="n">extract</span><span class="p">.</span><span class="n">setNegative</span> <span class="p">(</span><span class="nb">false</span><span class="p">);</span>

    <span class="c1">// Get the points associated with the planar surface</span>
    <span class="n">extract</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_plane</span><span class="p">);</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;PointCloud representing the planar component: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud_plane</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; data points.&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

    <span class="c1">// Remove the planar inliers, extract the rest</span>
    <span class="n">extract</span><span class="p">.</span><span class="n">setNegative</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>
    <span class="n">extract</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_f</span><span class="p">);</span>
    <span class="o">*</span><span class="n">cloud_filtered</span> <span class="o">=</span> <span class="o">*</span><span class="n">cloud_f</span><span class="p">;</span>
  <span class="p">}</span>

  <span class="c1">// Creating the KdTree object for the search method of the extraction</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">tree</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">tree</span><span class="o">-&gt;</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud_filtered</span><span class="p">);</span>

  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointIndices</span><span class="o">&gt;</span> <span class="n">cluster_indices</span><span class="p">;</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">EuclideanClusterExtraction</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">ec</span><span class="p">;</span>
  <span class="n">ec</span><span class="p">.</span><span class="n">setClusterTolerance</span> <span class="p">(</span><span class="mf">0.02</span><span class="p">);</span> <span class="c1">// 2cm</span>
  <span class="n">ec</span><span class="p">.</span><span class="n">setMinClusterSize</span> <span class="p">(</span><span class="mi">100</span><span class="p">);</span>
  <span class="n">ec</span><span class="p">.</span><span class="n">setMaxClusterSize</span> <span class="p">(</span><span class="mi">25000</span><span class="p">);</span>
  <span class="n">ec</span><span class="p">.</span><span class="n">setSearchMethod</span> <span class="p">(</span><span class="n">tree</span><span class="p">);</span>
  <span class="n">ec</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud_filtered</span><span class="p">);</span>
  <span class="n">ec</span><span class="p">.</span><span class="n">extract</span> <span class="p">(</span><span class="n">cluster_indices</span><span class="p">);</span>

  <span class="kt">int</span> <span class="n">j</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
  <span class="k">for</span> <span class="p">(</span><span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointIndices</span><span class="o">&gt;::</span><span class="n">const_iterator</span> <span class="n">it</span> <span class="o">=</span> <span class="n">cluster_indices</span><span class="p">.</span><span class="n">begin</span> <span class="p">();</span> <span class="n">it</span> <span class="o">!=</span> <span class="n">cluster_indices</span><span class="p">.</span><span class="n">end</span> <span class="p">();</span> <span class="o">++</span><span class="n">it</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud_cluster</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
    <span class="k">for</span> <span class="p">(</span><span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;::</span><span class="n">const_iterator</span> <span class="n">pit</span> <span class="o">=</span> <span class="n">it</span><span class="o">-&gt;</span><span class="n">indices</span><span class="p">.</span><span class="n">begin</span> <span class="p">();</span> <span class="n">pit</span> <span class="o">!=</span> <span class="n">it</span><span class="o">-&gt;</span><span class="n">indices</span><span class="p">.</span><span class="n">end</span> <span class="p">();</span> <span class="o">++</span><span class="n">pit</span><span class="p">)</span>
      <span class="n">cloud_cluster</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="n">cloud_filtered</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="o">*</span><span class="n">pit</span><span class="p">]);</span> <span class="c1">//*</span>
    <span class="n">cloud_cluster</span><span class="o">-&gt;</span><span class="n">width</span> <span class="o">=</span> <span class="n">cloud_cluster</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span>
    <span class="n">cloud_cluster</span><span class="o">-&gt;</span><span class="n">height</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>
    <span class="n">cloud_cluster</span><span class="o">-&gt;</span><span class="n">is_dense</span> <span class="o">=</span> <span class="nb">true</span><span class="p">;</span>

    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;PointCloud representing the Cluster: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud_cluster</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; data points.&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>
    <span class="n">std</span><span class="o">::</span><span class="n">stringstream</span> <span class="n">ss</span><span class="p">;</span>
    <span class="n">ss</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;cloud_cluster_&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">j</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;.pcd&quot;</span><span class="p">;</span>
    <span class="n">writer</span><span class="p">.</span><span class="n">write</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">(</span><span class="n">ss</span><span class="p">.</span><span class="n">str</span> <span class="p">(),</span> <span class="o">*</span><span class="n">cloud_cluster</span><span class="p">,</span> <span class="nb">false</span><span class="p">);</span> <span class="c1">//*</span>
    <span class="n">j</span><span class="o">++</span><span class="p">;</span>
  <span class="p">}</span>

  <span class="k">return</span> <span class="p">(</span><span class="mi">0</span><span class="p">);</span>
<span class="p">}</span>
</pre></div>
</td></tr></table></div>
</div>
<div class="section" id="the-explanation">
<h1>The Explanation</h1>
<p>Now, let’s break down the code piece by piece, skipping the obvious.</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span>    <span class="c1">// Read in the cloud data</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">PCDReader</span> <span class="n">reader</span><span class="p">;</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">),</span> <span class="n">cloud_f</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
    <span class="n">reader</span><span class="p">.</span><span class="n">read</span> <span class="p">(</span><span class="s">&quot;table_scene_lms400.pcd&quot;</span><span class="p">,</span> <span class="o">*</span><span class="n">cloud</span><span class="p">);</span>
    <span class="n">std</span><span class="o">::</span><span class="n">cout</span> <span class="o">&lt;&lt;</span> <span class="s">&quot;PointCloud before filtering has: &quot;</span> <span class="o">&lt;&lt;</span> <span class="n">cloud</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span> <span class="o">&lt;&lt;</span> <span class="s">&quot; data points.&quot;</span> <span class="o">&lt;&lt;</span> <span class="n">std</span><span class="o">::</span><span class="n">endl</span><span class="p">;</span>

              <span class="p">.</span>
              <span class="p">.</span>
              <span class="p">.</span>

    <span class="k">while</span> <span class="p">(</span><span class="n">cloud_filtered</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">()</span> <span class="o">&gt;</span> <span class="mf">0.3</span> <span class="o">*</span> <span class="n">nr_points</span><span class="p">)</span>
    <span class="p">{</span>

              <span class="p">.</span>
              <span class="p">.</span>
              <span class="p">.</span>

            <span class="c1">// Remove the plane inliers, extract the rest</span>
      <span class="n">extract</span><span class="p">.</span><span class="n">setNegative</span> <span class="p">(</span><span class="nb">true</span><span class="p">);</span>
      <span class="n">extract</span><span class="p">.</span><span class="n">filter</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_f</span><span class="p">);</span>
<span class="n">cloud_filtered</span> <span class="o">=</span> <span class="n">cloud_f</span><span class="p">;</span>
    <span class="p">}</span>
</pre></div>
</div>
<p>The code above is already described in other tutorials, so you can read the
explanation there (in particular <a class="reference internal" href="planar_segmentation.php#planar-segmentation"><span class="std std-ref">Plane model segmentation</span></a> and
<a class="reference internal" href="extract_indices.php#extract-indices"><span class="std std-ref">Extracting indices from a PointCloud</span></a>).</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span>  <span class="c1">// Creating the KdTree object for the search method of the extraction</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">tree</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
  <span class="n">tree</span><span class="o">-&gt;</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud_filtered</span><span class="p">);</span>
</pre></div>
</div>
<p>There we are creating a KdTree object for the search method of our extraction
algorithm.</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span>  <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointIndices</span><span class="o">&gt;</span> <span class="n">cluster_indices</span><span class="p">;</span>
</pre></div>
</div>
<p>Here we are creating a vector of <cite>PointIndices</cite>, which contain the actual index information in a <cite>vector&lt;int&gt;</cite>. The indices of each detected
cluster are saved here - please take note of the fact that <cite>cluster_indices</cite> is a
vector containing one instance of PointIndices for each detected cluster. So
<cite>cluster_indices[0]</cite> contains all indices of the first cluster in our point cloud.</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span>  <span class="n">pcl</span><span class="o">::</span><span class="n">EuclideanClusterExtraction</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="n">ec</span><span class="p">;</span>
  <span class="n">ec</span><span class="p">.</span><span class="n">setClusterTolerance</span> <span class="p">(</span><span class="mf">0.02</span><span class="p">);</span> <span class="c1">// 2cm</span>
  <span class="n">ec</span><span class="p">.</span><span class="n">setMinClusterSize</span> <span class="p">(</span><span class="mi">100</span><span class="p">);</span>
  <span class="n">ec</span><span class="p">.</span><span class="n">setMaxClusterSize</span> <span class="p">(</span><span class="mi">25000</span><span class="p">);</span>
  <span class="n">ec</span><span class="p">.</span><span class="n">setSearchMethod</span> <span class="p">(</span><span class="n">tree</span><span class="p">);</span>
  <span class="n">ec</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud_filtered</span><span class="p">);</span>
  <span class="n">ec</span><span class="p">.</span><span class="n">extract</span> <span class="p">(</span><span class="n">cluster_indices</span><span class="p">);</span>
</pre></div>
</div>
<p>Here we are creating a EuclideanClusterExtraction object with point type
PointXYZ since our point cloud is of type PointXYZ. We are also setting the
parameters and variables for the extraction.  Be careful setting the right
value for <strong>setClusterTolerance()</strong>. If you take a very small value, it can
happen that an actual <em>object</em> can be seen as multiple clusters. On the other
hand, if you set the value too high, it could happen, that multiple <em>objects</em>
are seen as one cluster. So our recommendation is to just test and try out
which value suits your dataset.</p>
<p>We impose that the clusters found must have at least <strong>setMinClusterSize()</strong>
points and maximum <strong>setMaxClusterSize()</strong> points.</p>
<p>Now we extracted the clusters out of our point cloud and saved the indices in
<strong>cluster_indices</strong>. To separate each cluster out of the <cite>vector&lt;PointIndices&gt;</cite>
we have to iterate through <em>cluster_indices</em>, create a new <cite>PointCloud</cite> for
each entry and write all points of the current cluster in the <cite>PointCloud</cite>.</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span>  <span class="kt">int</span> <span class="n">j</span> <span class="o">=</span> <span class="mi">0</span><span class="p">;</span>
  <span class="k">for</span> <span class="p">(</span><span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointIndices</span><span class="o">&gt;::</span><span class="n">const_iterator</span> <span class="n">it</span> <span class="o">=</span> <span class="n">cluster_indices</span><span class="p">.</span><span class="n">begin</span> <span class="p">();</span> <span class="n">it</span> <span class="o">!=</span> <span class="n">cluster_indices</span><span class="p">.</span><span class="n">end</span> <span class="p">();</span> <span class="o">++</span><span class="n">it</span><span class="p">)</span>
  <span class="p">{</span>
    <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud_cluster</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>
    <span class="k">for</span> <span class="p">(</span><span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;::</span><span class="n">const_iterator</span> <span class="n">pit</span> <span class="o">=</span> <span class="n">it</span><span class="o">-&gt;</span><span class="n">indices</span><span class="p">.</span><span class="n">begin</span> <span class="p">();</span> <span class="n">pit</span> <span class="o">!=</span> <span class="n">it</span><span class="o">-&gt;</span><span class="n">indices</span><span class="p">.</span><span class="n">end</span> <span class="p">();</span> <span class="o">++</span><span class="n">pit</span><span class="p">)</span>
      <span class="n">cloud_cluster</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">push_back</span> <span class="p">(</span><span class="n">cloud_filtered</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">[</span><span class="o">*</span><span class="n">pit</span><span class="p">]);</span> <span class="c1">//*</span>
    <span class="n">cloud_cluster</span><span class="o">-&gt;</span><span class="n">width</span> <span class="o">=</span> <span class="n">cloud_cluster</span><span class="o">-&gt;</span><span class="n">points</span><span class="p">.</span><span class="n">size</span> <span class="p">();</span>
    <span class="n">cloud_cluster</span><span class="o">-&gt;</span><span class="n">height</span> <span class="o">=</span> <span class="mi">1</span><span class="p">;</span>
    <span class="n">cloud_cluster</span><span class="o">-&gt;</span><span class="n">is_dense</span> <span class="o">=</span> <span class="nb">true</span><span class="p">;</span>
</pre></div>
</div>
</div>
<div class="section" id="compiling-and-running-the-program">
<h1>Compiling and running the program</h1>
<p>Add the following lines to your CMakeLists.txt</p>
<div class="highlight-cmake notranslate"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre> 1
 2
 3
 4
 5
 6
 7
 8
 9
10
11
12</pre></div></td><td class="code"><div class="highlight"><pre><span></span><span class="nb">cmake_minimum_required</span><span class="p">(</span><span class="s">VERSION</span> <span class="s">2.8</span> <span class="s">FATAL_ERROR</span><span class="p">)</span>

<span class="nb">project</span><span class="p">(</span><span class="s">cluster_extraction</span><span class="p">)</span>

<span class="nb">find_package</span><span class="p">(</span><span class="s">PCL</span> <span class="s">1.2</span> <span class="s">REQUIRED</span><span class="p">)</span>

<span class="nb">include_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_INCLUDE_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">link_directories</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_LIBRARY_DIRS</span><span class="o">}</span><span class="p">)</span>
<span class="nb">add_definitions</span><span class="p">(</span><span class="o">${</span><span class="nv">PCL_DEFINITIONS</span><span class="o">}</span><span class="p">)</span>

<span class="nb">add_executable</span> <span class="p">(</span><span class="s">cluster_extraction</span> <span class="s">cluster_extraction.cpp</span><span class="p">)</span>
<span class="nb">target_link_libraries</span> <span class="p">(</span><span class="s">cluster_extraction</span> <span class="o">${</span><span class="nv">PCL_LIBRARIES</span><span class="o">}</span><span class="p">)</span>
</pre></div>
</td></tr></table></div>
<p>After you have made the executable, you can run it. Simply do:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span>$ ./cluster_extraction
</pre></div>
</div>
<p>You will see something similar to:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span><span class="n">PointCloud</span> <span class="n">before</span> <span class="n">filtering</span> <span class="n">has</span><span class="p">:</span> <span class="mi">460400</span> <span class="n">data</span> <span class="n">points</span><span class="o">.</span>
<span class="n">PointCloud</span> <span class="n">after</span> <span class="n">filtering</span> <span class="n">has</span><span class="p">:</span> <span class="mi">41049</span> <span class="n">data</span> <span class="n">points</span><span class="o">.</span>
<span class="p">[</span><span class="n">SACSegmentation</span><span class="p">::</span><span class="n">initSACModel</span><span class="p">]</span> <span class="n">Using</span> <span class="n">a</span> <span class="n">model</span> <span class="n">of</span> <span class="nb">type</span><span class="p">:</span> <span class="n">SACMODEL_PLANE</span>
<span class="p">[</span><span class="n">SACSegmentation</span><span class="p">::</span><span class="n">initSAC</span><span class="p">]</span> <span class="n">Using</span> <span class="n">a</span> <span class="n">method</span> <span class="n">of</span> <span class="nb">type</span><span class="p">:</span> <span class="n">SAC_RANSAC</span> <span class="k">with</span> <span class="n">a</span> <span class="n">model</span> <span class="n">threshold</span> <span class="n">of</span> <span class="mf">0.020000</span>
<span class="p">[</span><span class="n">SACSegmentation</span><span class="p">::</span><span class="n">initSAC</span><span class="p">]</span> <span class="n">Setting</span> <span class="n">the</span> <span class="n">maximum</span> <span class="n">number</span> <span class="n">of</span> <span class="n">iterations</span> <span class="n">to</span> <span class="mi">100</span>
<span class="n">PointCloud</span> <span class="n">representing</span> <span class="n">the</span> <span class="n">planar</span> <span class="n">component</span><span class="p">:</span> <span class="mi">20522</span> <span class="n">data</span> <span class="n">points</span><span class="o">.</span>
<span class="p">[</span><span class="n">SACSegmentation</span><span class="p">::</span><span class="n">initSACModel</span><span class="p">]</span> <span class="n">Using</span> <span class="n">a</span> <span class="n">model</span> <span class="n">of</span> <span class="nb">type</span><span class="p">:</span> <span class="n">SACMODEL_PLANE</span>
<span class="p">[</span><span class="n">SACSegmentation</span><span class="p">::</span><span class="n">initSAC</span><span class="p">]</span> <span class="n">Using</span> <span class="n">a</span> <span class="n">method</span> <span class="n">of</span> <span class="nb">type</span><span class="p">:</span> <span class="n">SAC_RANSAC</span> <span class="k">with</span> <span class="n">a</span> <span class="n">model</span> <span class="n">threshold</span> <span class="n">of</span> <span class="mf">0.020000</span>
<span class="p">[</span><span class="n">SACSegmentation</span><span class="p">::</span><span class="n">initSAC</span><span class="p">]</span> <span class="n">Setting</span> <span class="n">the</span> <span class="n">maximum</span> <span class="n">number</span> <span class="n">of</span> <span class="n">iterations</span> <span class="n">to</span> <span class="mi">100</span>
<span class="n">PointCloud</span> <span class="n">representing</span> <span class="n">the</span> <span class="n">planar</span> <span class="n">component</span><span class="p">:</span> <span class="mi">12429</span> <span class="n">data</span> <span class="n">points</span><span class="o">.</span>
<span class="n">PointCloud</span> <span class="n">representing</span> <span class="n">the</span> <span class="n">Cluster</span><span class="p">:</span> <span class="mi">4883</span> <span class="n">data</span> <span class="n">points</span><span class="o">.</span>
<span class="n">PointCloud</span> <span class="n">representing</span> <span class="n">the</span> <span class="n">Cluster</span><span class="p">:</span> <span class="mi">1386</span> <span class="n">data</span> <span class="n">points</span><span class="o">.</span>
<span class="n">PointCloud</span> <span class="n">representing</span> <span class="n">the</span> <span class="n">Cluster</span><span class="p">:</span> <span class="mi">320</span> <span class="n">data</span> <span class="n">points</span><span class="o">.</span>
<span class="n">PointCloud</span> <span class="n">representing</span> <span class="n">the</span> <span class="n">Cluster</span><span class="p">:</span> <span class="mi">290</span> <span class="n">data</span> <span class="n">points</span><span class="o">.</span>
<span class="n">PointCloud</span> <span class="n">representing</span> <span class="n">the</span> <span class="n">Cluster</span><span class="p">:</span> <span class="mi">120</span> <span class="n">data</span> <span class="n">points</span><span class="o">.</span>
</pre></div>
</div>
<p>You can also look at your outputs cloud_cluster_0.pcd, cloud_cluster_1.pcd,
cloud_cluster_2.pcd, cloud_cluster_3.pcd and cloud_cluster_4.pcd:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span>$ ./pcl_viewer cloud_cluster_0.pcd cloud_cluster_1.pcd cloud_cluster_2.pcd cloud_cluster_3.pcd cloud_cluster_4.pcd
</pre></div>
</div>
<p>You are now able to see the different clusters in one viewer. You should see
something similar to this:</p>
<img alt="Output Cluster Extraction" class="align-center" src="_images/cluster_extraction.jpg" />
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