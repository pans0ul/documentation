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
    <title>Estimating Surface Normals in a PointCloud &#8212; PCL 0.0 documentation</title>
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
            
  <div class="section" id="estimating-surface-normals-in-a-pointcloud">
<span id="normal-estimation"></span><h1>Estimating Surface Normals in a PointCloud</h1>
<p>Surface normals are important properties of a geometric surface, and are
heavily used in many areas such as computer graphics applications, to apply the
correct light sources that generate shadings and other visual effects.</p>
<p>Given a geometric surface, it’s usually trivial to infer the direction of the
normal at a certain point on the surface as the vector perpendicular to the
surface in that point. However, since the point cloud datasets that we acquire
represent a set of point samples on the real surface, there are two
possibilities:</p>
<blockquote>
<div><ul class="simple">
<li>obtain the underlying surface from the acquired point cloud dataset, using
surface meshing techniques, and then compute the surface normals from the
mesh;</li>
<li>use approximations to infer the surface normals from the point cloud dataset
directly.</li>
</ul>
</div></blockquote>
<p>This tutorial will address the latter, that is, given a point cloud dataset,
directly compute the surface normals at each point in the cloud.</p>
<iframe width="425" height="349" src="http://www.youtube.com/embed/x1FSssJrfik" frameborder="0" allowfullscreen></iframe></div>
<div class="section" id="theoretical-primer">
<h1>Theoretical primer</h1>
<p>Though many different normal estimation methods exist, the one that we will
concentrate on this tutorial is one of the simplest, and is formulated as
follows. The problem of determining the normal to a point on the surface is
approximated by the problem of estimating the normal of a plane tangent to the
surface, which in turn becomes a least-square plane fitting estimation problem.</p>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">For more information, including the mathematical equations of the least-squares problem, see <a class="reference internal" href="how_features_work.php#rusudissertation" id="id1">[RusuDissertation]</a>.</p>
</div>
<p>The solution for estimating the surface normal is therefore reduced to an
analysis of the eigenvectors and eigenvalues (or PCA – Principal Component
Analysis) of a covariance matrix created from the nearest neighbors of the
query point. More specifically, for each point <img class="math" src="_images/math/1cd445b90bca7b1b01b56133314dd58344781f38.png" alt="\boldsymbol{p}_i"/>, we
assemble the covariance matrix <img class="math" src="_images/math/ae46a7ef5ecad0321ac8d545ea55567f47f45b64.png" alt="\mathcal{C}"/> as follows:</p>
<div class="math">
<p><img src="_images/math/c36f362110193c094424cd5dd0ead4bab698a42f.png" alt="\mathcal{C} = \frac{1}{k}\sum_{i=1}^{k}{\cdot (\boldsymbol{p}_i-\overline{\boldsymbol{p}})\cdot(\boldsymbol{p}_i-\overline{\boldsymbol{p}})^{T}}, ~\mathcal{C} \cdot \vec{{\mathsf v}_j} = \lambda_j \cdot \vec{{\mathsf v}_j},~ j \in \{0, 1, 2\}"/></p>
</div><p>Where <img class="math" src="_images/math/0b7c1e16a3a8a849bb8ffdcdbf86f65fd1f30438.png" alt="k"/> is the number of point neighbors considered <em>in the
neighborhood of</em> <img class="math" src="_images/math/1cd445b90bca7b1b01b56133314dd58344781f38.png" alt="\boldsymbol{p}_i"/>, <img class="math" src="_images/math/0a3f976725451333756660ef10b4bdc9073f4560.png" alt="\overline{\boldsymbol{p}}"/>
represents the 3D centroid of the nearest neighbors, <img class="math" src="_images/math/ab09ea3d9c6a9eee32d16f4d0fde9af8f165379e.png" alt="\lambda_j"/> is the
<img class="math" src="_images/math/6b21e0b0899a0d2879d3b8019087fa630bab4ea2.png" alt="j"/>-th eigenvalue of the covariance matrix, and <img class="math" src="_images/math/ed456fffe3ec6c74c7e575f93f572f31099e6159.png" alt="\vec{{\mathsf v}_j}"/>
the <img class="math" src="_images/math/6b21e0b0899a0d2879d3b8019087fa630bab4ea2.png" alt="j"/>-th eigenvector.</p>
<p>To estimate a covariance matrix from a set of points in PCL, you can use:</p>
<div class="highlight-cpp notranslate"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre> 1
 2
 3
 4
 5
 6
 7
 8
 9
10</pre></div></td><td class="code"><div class="highlight"><pre><span></span>  <span class="c1">// Placeholder for the 3x3 covariance matrix at each surface patch</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">Matrix3f</span> <span class="n">covariance_matrix</span><span class="p">;</span>
  <span class="c1">// 16-bytes aligned placeholder for the XYZ centroid of a surface patch</span>
  <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector4f</span> <span class="n">xyz_centroid</span><span class="p">;</span>

  <span class="c1">// Estimate the XYZ centroid</span>
  <span class="n">compute3DCentroid</span> <span class="p">(</span><span class="n">cloud</span><span class="p">,</span> <span class="n">xyz_centroid</span><span class="p">);</span>

  <span class="c1">// Compute the 3x3 covariance matrix</span>
  <span class="n">computeCovarianceMatrix</span> <span class="p">(</span><span class="n">cloud</span><span class="p">,</span> <span class="n">xyz_centroid</span><span class="p">,</span> <span class="n">covariance_matrix</span><span class="p">);</span>
</pre></div>
</td></tr></table></div>
<p>In general, because there is no mathematical way to solve for the sign of the
normal, its orientation computed via Principal Component Analysis (PCA) as
shown above is ambiguous, and not consistently oriented over an entire point
cloud dataset. The figure below presents these effects on two sections of a
larger dataset representing a part of a kitchen environment. The right part of
the figure presents the Extended Gaussian Image (EGI), also known as the normal
sphere, which describes the orientation of all normals from the point cloud.
Since the datasets are 2.5D and have thus been acquired from a single
viewpoint, normals should be present only on half of the sphere in the EGI.
However, due to the orientation inconsistency, they are spread across the
entire sphere.</p>
<a class="reference internal image-reference" href="_images/unflipped_scene1.jpg"><img alt="_images/unflipped_scene1.jpg" src="_images/unflipped_scene1.jpg" style="height: 200px;" /></a>
<a class="reference internal image-reference" href="_images/unflipped_scene2.jpg"><img alt="_images/unflipped_scene2.jpg" src="_images/unflipped_scene2.jpg" style="height: 200px;" /></a>
<a class="reference internal image-reference" href="_images/unflipped_sphere.jpg"><img alt="_images/unflipped_sphere.jpg" src="_images/unflipped_sphere.jpg" style="height: 200px;" /></a>
<p>The solution to this problem is trivial if the viewpoint <img class="math" src="_images/math/38c9a4fbf3958460af585eafa91f1efebfc703fc.png" alt="{\mathsf v}_p"/>
is in fact known. To orient all normals <img class="math" src="_images/math/5e2e3a03dd62b2ce33cd9829aea9fa04f0255cbe.png" alt="\vec{\boldsymbol{n}}_i"/>
consistently towards the viewpoint, they need to satisfy the equation:</p>
<div class="math">
<p><img src="_images/math/c7a372118ca0d394b306ebbf1da06cc2c9c16080.png" alt="\vec{\boldsymbol{n}}_i \cdot ({\mathsf v}_p - \boldsymbol{p}_i) &gt; 0"/></p>
</div><p>The figure below presents the results after all normals in the datasets from
the above figure have been consistently oriented towards the viewpoint.</p>
<a class="reference internal image-reference" href="_images/flipped_scene1.jpg"><img alt="_images/flipped_scene1.jpg" src="_images/flipped_scene1.jpg" style="height: 200px;" /></a>
<a class="reference internal image-reference" href="_images/flipped_scene2.jpg"><img alt="_images/flipped_scene2.jpg" src="_images/flipped_scene2.jpg" style="height: 200px;" /></a>
<a class="reference internal image-reference" href="_images/flipped_sphere.jpg"><img alt="_images/flipped_sphere.jpg" src="_images/flipped_sphere.jpg" style="height: 200px;" /></a>
<p>To re-orient a given point normal manually in PCL, you can use:</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span><span class="n">flipNormalTowardsViewpoint</span> <span class="p">(</span><span class="k">const</span> <span class="n">PointT</span> <span class="o">&amp;</span><span class="n">point</span><span class="p">,</span> <span class="kt">float</span> <span class="n">vp_x</span><span class="p">,</span> <span class="kt">float</span> <span class="n">vp_y</span><span class="p">,</span> <span class="kt">float</span> <span class="n">vp_z</span><span class="p">,</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector4f</span> <span class="o">&amp;</span><span class="n">normal</span><span class="p">);</span>
</pre></div>
</div>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">If the dataset has multiple acquisition viewpoints, then the above normal re-orientation method does not hold, and more complex algorithms need to be implemented. Please see <a class="reference internal" href="how_features_work.php#rusudissertation" id="id2">[RusuDissertation]</a> for more information.</p>
</div>
</div>
<div class="section" id="selecting-the-right-scale">
<h1>Selecting the right scale</h1>
<p>As previously explained, a surface normal at a point needs to be estimated from
the surrounding point neighborhood support of the point (also called
<strong>k-neighborhood</strong>).</p>
<p>The specifics of the nearest-neighbor estimation problem raise the question of
the <em>right scale factor</em>: given a sampled point cloud dataset , what are the
correct <strong>k</strong> (given via <strong>pcl::Feature::setKSearch</strong>) or <strong>r</strong> (given via
<strong>pcl::Feature::setRadiusSearch</strong>) values that should be used in determining
the set of nearest neighbors of a point?</p>
<p>This issue is of extreme importance and constitutes a limiting factor in the
automatic estimation (i.e., without user given thresholds) of a point feature
representation. To better illustrate this issue, the figure below presents the
effects of selecting a smaller scale (i.e., small <strong>r</strong> or <strong>k</strong>) versus a
larger scale (i.e., large <strong>r</strong> or <strong>k</strong>). The left part of the figures depicts
a reasonable well chosen scale factor, with estimated surface normals
approximately perpendicular for the two planar surfaces and small edges
visible all across the table. If the scale factor however is too big (right
part), and thus the set of neighbors is larger covering points from adjacent
surfaces, the estimated point feature representations get distorted, with
rotated surface normals at the edges of the two planar surfaces, and smeared
edges and suppressed fine details.</p>
<a class="reference internal image-reference" href="_images/normals_different_radii.jpg"><img alt="_images/normals_different_radii.jpg" src="_images/normals_different_radii.jpg" style="height: 180px;" /></a>
<a class="reference internal image-reference" href="_images/curvature_different_radii.jpg"><img alt="_images/curvature_different_radii.jpg" src="_images/curvature_different_radii.jpg" style="height: 180px;" /></a>
<p>Without going into too many details, it suffices to assume that for now, the
scale for the determination of a point’s neighborhood has to be selected based
on the level of detail required by the application. Simply put, if the
curvature at the edge between the handle of a mug and the cylindrical part is
important, the scale factor needs to be small enough to capture those details,
and large otherwise.</p>
</div>
<div class="section" id="estimating-the-normals">
<h1>Estimating the normals</h1>
<p>Though an example of normal estimation has already been given in
<a class="reference internal" href="index.php#features-tutorial"><span class="std std-ref">Features</span></a>, we will revise one of them here for the purpose of
better explaining what goes on behind the scenes.</p>
<p>The following code snippet will estimate a set of surface normals for all the
points in the input dataset.</p>
<div class="highlight-cpp notranslate"><table class="highlighttable"><tr><td class="linenos"><div class="linenodiv"><pre> 1
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
28</pre></div></td><td class="code"><div class="highlight"><pre><span></span><span class="cp">#include</span> <span class="cpf">&lt;pcl/point_types.h&gt;</span><span class="cp"></span>
<span class="cp">#include</span> <span class="cpf">&lt;pcl/features/normal_3d.h&gt;</span><span class="cp"></span>

<span class="p">{</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span><span class="p">);</span>

  <span class="p">...</span> <span class="n">read</span><span class="p">,</span> <span class="n">pass</span> <span class="n">in</span> <span class="n">or</span> <span class="n">create</span> <span class="n">a</span> <span class="n">point</span> <span class="n">cloud</span> <span class="p">...</span>

  <span class="c1">// Create the normal estimation class, and pass the input dataset to it</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">NormalEstimation</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="p">,</span> <span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;</span> <span class="n">ne</span><span class="p">;</span>
  <span class="n">ne</span><span class="p">.</span><span class="n">setInputCloud</span> <span class="p">(</span><span class="n">cloud</span><span class="p">);</span>

  <span class="c1">// Create an empty kdtree representation, and pass it to the normal estimation object.</span>
  <span class="c1">// Its content will be filled inside the object, based on the given input dataset (as no other search surface is given).</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">tree</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">search</span><span class="o">::</span><span class="n">KdTree</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">PointXYZ</span><span class="o">&gt;</span> <span class="p">());</span>
  <span class="n">ne</span><span class="p">.</span><span class="n">setSearchMethod</span> <span class="p">(</span><span class="n">tree</span><span class="p">);</span>

  <span class="c1">// Output datasets</span>
  <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;::</span><span class="n">Ptr</span> <span class="n">cloud_normals</span> <span class="p">(</span><span class="k">new</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">pcl</span><span class="o">::</span><span class="n">Normal</span><span class="o">&gt;</span><span class="p">);</span>

  <span class="c1">// Use all neighbors in a sphere of radius 3cm</span>
  <span class="n">ne</span><span class="p">.</span><span class="n">setRadiusSearch</span> <span class="p">(</span><span class="mf">0.03</span><span class="p">);</span>

  <span class="c1">// Compute the features</span>
  <span class="n">ne</span><span class="p">.</span><span class="n">compute</span> <span class="p">(</span><span class="o">*</span><span class="n">cloud_normals</span><span class="p">);</span>

  <span class="c1">// cloud_normals-&gt;points.size () should have the same size as the input cloud-&gt;points.size ()*</span>
<span class="p">}</span>
</pre></div>
</td></tr></table></div>
<p>The actual <strong>compute</strong> call from the <strong>NormalEstimation</strong> class does nothing internally but:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span><span class="k">for</span> <span class="n">each</span> <span class="n">point</span> <span class="n">p</span> <span class="ow">in</span> <span class="n">cloud</span> <span class="n">P</span>

  <span class="mf">1.</span> <span class="n">get</span> <span class="n">the</span> <span class="n">nearest</span> <span class="n">neighbors</span> <span class="n">of</span> <span class="n">p</span>

  <span class="mf">2.</span> <span class="n">compute</span> <span class="n">the</span> <span class="n">surface</span> <span class="n">normal</span> <span class="n">n</span> <span class="n">of</span> <span class="n">p</span>

  <span class="mf">3.</span> <span class="n">check</span> <span class="k">if</span> <span class="n">n</span> <span class="ow">is</span> <span class="n">consistently</span> <span class="n">oriented</span> <span class="n">towards</span> <span class="n">the</span> <span class="n">viewpoint</span> <span class="ow">and</span> <span class="n">flip</span> <span class="n">otherwise</span>
</pre></div>
</div>
<p>The viewpoint is by default (0,0,0) and can be changed with:</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span><span class="n">setViewPoint</span> <span class="p">(</span><span class="kt">float</span> <span class="n">vpx</span><span class="p">,</span> <span class="kt">float</span> <span class="n">vpy</span><span class="p">,</span> <span class="kt">float</span> <span class="n">vpz</span><span class="p">);</span>
</pre></div>
</div>
<p>To compute a single point normal, use:</p>
<div class="highlight-cpp notranslate"><div class="highlight"><pre><span></span><span class="n">computePointNormal</span> <span class="p">(</span><span class="k">const</span> <span class="n">pcl</span><span class="o">::</span><span class="n">PointCloud</span><span class="o">&lt;</span><span class="n">PointInT</span><span class="o">&gt;</span> <span class="o">&amp;</span><span class="n">cloud</span><span class="p">,</span> <span class="k">const</span> <span class="n">std</span><span class="o">::</span><span class="n">vector</span><span class="o">&lt;</span><span class="kt">int</span><span class="o">&gt;</span> <span class="o">&amp;</span><span class="n">indices</span><span class="p">,</span> <span class="n">Eigen</span><span class="o">::</span><span class="n">Vector4f</span> <span class="o">&amp;</span><span class="n">plane_parameters</span><span class="p">,</span> <span class="kt">float</span> <span class="o">&amp;</span><span class="n">curvature</span><span class="p">);</span>
</pre></div>
</div>
<p>Where <em>cloud</em> is the input point cloud that contains the points, <em>indices</em>
represents the set of k-nearest neighbors from <em>cloud</em>, and plane_parameters
and curvature represent the output of the normal estimation, with
<em>plane_parameters</em> holding the normal (nx, ny, nz) on the first 3 coordinates,
and the fourth coordinate is D = nc . p_plane (centroid here) + p. The output surface curvature is estimated as a relationship between the eigenvalues of the covariance matrix (as presented above), as:</p>
<div class="math">
<p><img src="_images/math/bf4abc16cb747367d4ef89d02b8339718f5bafe6.png" alt="\sigma = \frac{\lambda_0}{\lambda_0 + \lambda_1 + \lambda_2}"/></p>
</div></div>
<div class="section" id="speeding-normal-estimation-with-openmp">
<h1>Speeding Normal Estimation with OpenMP</h1>
<p>For the speed-savvy users, PCL provides an additional implementation of surface
normal estimation which uses multi-core/multi-threaded paradigms using OpenMP
to speed the computation. The name of the class is
<strong>pcl::NormalEstimationOMP</strong>, and its API is 100% compatible to the
single-threaded <strong>pcl::NormalEstimation</strong>, which makes it suitable as a drop-in
replacement. On a system with 8 cores, you should get anything between 6-8
times faster computation times.</p>
<div class="admonition note">
<p class="first admonition-title">Note</p>
<p class="last">If your dataset is organized (e.g., acquired using a TOF camera, stereo camera, etc – that is, it has a width and a height), for even faster results see the <a class="reference internal" href="normal_estimation_using_integral_images.php#normal-estimation-using-integral-images"><span class="std std-ref">Normal Estimation Using Integral Images</span></a>.</p>
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