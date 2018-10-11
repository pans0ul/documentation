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
    <title>PCL 2.x API consideration guide</title>
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
            
  <div class="section" id="pcl-2-x-api-consideration-guide">
<span id="pcl2"></span><h1>PCL 2.x API consideration guide</h1>
<p>With the PCL 1.x API locked and a few releases already underway, it’s time to
consider what the next generation of libraries should look like. This document
discusses a series of changes to the current API, from base classes to higher
level algorithms.</p>
<div class="section" id="major-changes">
<h2>Major changes</h2>
<div class="section" id="pcl-pointcloud">
<h3>1.1 pcl::PointCloud</h3>
<p>The <a href="#id1"><span class="problematic" id="id2">:pcl:`PointCloud &lt;pcl::PointCloud&gt;`</span></a> class represents the base class in PCL
for holding <strong>nD</strong> (n dimensional) data.</p>
<dl class="docutils">
<dt>The 1.x API includes the following data members:</dt>
<dd><ul class="first last simple">
<li><a href="#id3"><span class="problematic" id="id4">:pcl:`PCLHeader &lt;pcl::PCLHeader&gt;`</span></a> (coming from ROS)<ul>
<li><strong>uint32_t</strong> <a href="#id5"><span class="problematic" id="id6">:pcl:`seq &lt;pcl::PCLHeader::seq&gt;`</span></a> - a sequence number</li>
<li><strong>uint64_t</strong> <a href="#id7"><span class="problematic" id="id8">:pcl:`stamp &lt;pcl::PCLHeader::stamp&gt;`</span></a> - a timestamp associated with the time when the data was acquired</li>
<li><strong>std::string</strong> <a href="#id9"><span class="problematic" id="id10">:pcl:`frame_id &lt;pcl::PCLHeader::frame_id&gt;`</span></a> - a TF frame ID</li>
</ul>
</li>
<li><strong>std::vector&lt;T&gt;</strong> <a href="#id11"><span class="problematic" id="id12">:pcl:`points &lt;pcl::PointCloud::points&gt;`</span></a> - a std C++ vector of T data. T can be a structure of any of the types defined in <cite>point_types.h</cite>.</li>
<li><strong>uint32_t</strong> <a href="#id13"><span class="problematic" id="id14">:pcl:`width &lt;pcl::PointCloud::width&gt;`</span></a> - the width (for organized datasets) of the data. Set to the number of points for unorganized data.</li>
<li><strong>uint32_t</strong> <a href="#id15"><span class="problematic" id="id16">:pcl:`height &lt;pcl::PointCloud::height&gt;`</span></a> - the height (for organized datasets) of the data. Set to 1 for unorganized data.</li>
<li><strong>bool</strong> <a href="#id17"><span class="problematic" id="id18">:pcl:`is_dense &lt;pcl::PointCloud::is_dense&gt;`</span></a> - true if the data contains only valid numbers (e.g., no NaN or -/+Inf, etc). False otherwise.</li>
<li><strong>Eigen::Vector4f</strong> <a href="#id19"><span class="problematic" id="id20">:pcl:`sensor_origin_ &lt;pcl::PointCloud::sensor_origin_&gt;`</span></a> - the origin (pose) of the acquisition sensor in the current data coordinate system.</li>
<li><strong>Eigen::Quaternionf</strong> <a href="#id21"><span class="problematic" id="id22">:pcl:`sensor_orientation_ &lt;pcl::PointCloud::sensor_orientation_&gt;`</span></a> - the origin (orientation) of the acquisition sensor in the current data coordinate system.</li>
</ul>
</dd>
</dl>
<p>Proposals for the 2.x API:</p>
<blockquote>
<div><ul>
<li><p class="first">drop templating on point types, thus making <a href="#id23"><span class="problematic" id="id24">:pcl:`PointCloud &lt;pcl::PointCloud&gt;`</span></a> template free</p>
</li>
<li><p class="first">drop the <a href="#id25"><span class="problematic" id="id26">:pcl:`PCLHeader &lt;pcl::PCLHeader&gt;`</span></a> structure, or consolidate all the above information (width, height, is_dense, sensor_origin, sensor_orientation) into a single struct</p>
</li>
<li><p class="first">make sure we can access a slice of the data as a <em>2D image</em>, thus allowing fast 2D displaying, [u, v] operations, etc</p>
</li>
<li><p class="first">make sure we can access a slice of the data as a subpoint cloud: only certain points are chosen from the main point cloud</p>
</li>
<li><p class="first">implement channels (of a single type!) as data holders, e.g.:
* cloud[“xyz”] =&gt; gets all 3D x,y,z data
* cloud[“normals”] =&gt; gets all surface normal data
* etc</p>
</li>
<li><p class="first">internals should be hidden : only accessors (begin, end …) are public, this facilitating the change of the underlying structure</p>
</li>
<li><p class="first">Capability to construct point cloud types containing the necessary channels
<em>at runtime</em>. This will be particularly useful for run-time configuration of
input sensors and for reading point clouds from files, which may contain a
variety of point cloud layouts not known until the file is opened.</p>
</li>
<li><p class="first">Complete traits system to identify what data/channels a cloud stores at
runtime, facilitating decision making in software that uses PCL. (e.g.
generic component wrappers.)</p>
</li>
<li><p class="first">Stream-based IO sub-system to allow developers to load a stream of point
clouds and “play” them through their algorithm(s), as well as easily capture
a stream of point clouds (e.g. from a Kinect). Perhaps based on
Boost::Iostreams.</p>
</li>
<li><p class="first">Given the experience on <a class="reference external" href="https://github.com/ethz-asl/libpointmatcher">libpointmatcher</a>,
we (François Pomerleau and Stéphane Magnenat) propose the following data structures:</p>
<div class="highlight-default notranslate"><div class="highlight"><pre><span></span><span class="n">cloud</span> <span class="o">=</span> <span class="nb">map</span><span class="o">&lt;</span><span class="n">space_identifier</span><span class="p">,</span> <span class="n">space</span><span class="o">&gt;</span>
<span class="n">space</span> <span class="o">=</span> <span class="nb">tuple</span><span class="o">&lt;</span><span class="nb">type</span><span class="p">,</span> <span class="n">components_identifiers</span><span class="p">,</span> <span class="n">data_matrix</span><span class="o">&gt;</span>
<span class="n">components_identifiers</span> <span class="o">=</span> <span class="n">vector</span><span class="o">&lt;</span><span class="n">component_identifier</span><span class="o">&gt;</span>
<span class="n">data_matrix</span> <span class="o">=</span> <span class="n">Eigen</span> <span class="n">matrix</span>
<span class="n">space_identifier</span> <span class="o">=</span> <span class="n">string</span> <span class="k">with</span> <span class="n">standardised</span> <span class="n">naming</span> <span class="p">(</span><span class="n">pos</span><span class="p">,</span> <span class="n">normals</span><span class="p">,</span> <span class="n">color</span><span class="p">,</span> <span class="n">etc</span><span class="o">.</span><span class="p">)</span>
<span class="n">component_identifier</span> <span class="o">=</span> <span class="n">string</span> <span class="k">with</span> <span class="n">standardised</span> <span class="n">naming</span> <span class="p">(</span><span class="n">x</span><span class="p">,</span> <span class="n">y</span><span class="p">,</span> <span class="n">r</span><span class="p">,</span> <span class="n">g</span><span class="p">,</span> <span class="n">b</span><span class="p">,</span> <span class="n">etc</span><span class="o">.</span><span class="p">)</span>
<span class="nb">type</span> <span class="o">=</span> <span class="nb">type</span> <span class="n">of</span> <span class="n">space</span><span class="p">,</span> <span class="n">underlying</span> <span class="n">scalar</span> <span class="nb">type</span> <span class="o">+</span> <span class="n">distance</span> <span class="n">definition</span> <span class="p">(</span><span class="nb">float</span> <span class="k">with</span> <span class="n">euclidean</span> <span class="mi">2</span><span class="o">-</span><span class="n">norm</span> <span class="n">distance</span><span class="p">,</span> <span class="nb">float</span> <span class="n">representing</span> <span class="n">gaussians</span> <span class="k">with</span> <span class="n">Mahalanobis</span> <span class="n">distance</span><span class="p">,</span> <span class="n">binary</span> <span class="k">with</span> <span class="n">manhattan</span> <span class="n">distance</span><span class="p">,</span> <span class="nb">float</span> <span class="k">with</span> <span class="n">euclidean</span> <span class="n">infinity</span> <span class="n">norm</span> <span class="n">distance</span><span class="p">,</span> <span class="n">etc</span><span class="o">.</span><span class="p">)</span>
</pre></div>
</div>
<dl class="docutils">
<dt>For instance, a simple point + color scenario could be::</dt>
<dd><p class="first last">cloud = { “pos” =&gt; pos_space, “color” =&gt; color_space }
pos_space = ( “float with euclidean 2-norm distance”, { “x”, “y”, “z” }, [[(0.3,0,1.3) , … , (1.2,3.1,2)], … , [(1,0.3,1) , … , (2,0,3.5)] )
color_space = ( “uint8 with rgb distance”, { “r”, “g”, “b” }, [[(0,255,0), … , (128,255,32)] … [(12,54,31) … (255,0,192)]] )</p>
</dd>
</dl>
</li>
</ul>
</div></blockquote>
</div>
<div class="section" id="pointtypes">
<h3>1.2 PointTypes</h3>
<blockquote>
<div><ol class="arabic simple">
<li>Eigen::Vector4f or Eigen::Vector3f ??</li>
<li>Large points cause significant performance penalty for GPU. Let’s assume that point sizes up to 16 bytes are suitable. This is some compromise between SOA and AOS. Structures like pcl::Normal (size = 32) is not desirable. SOA is better in this case.</li>
</ol>
</div></blockquote>
</div>
<div class="section" id="gpu-support">
<h3>1.3 GPU support</h3>
<blockquote>
<div><ol class="arabic">
<li><p class="first">Containers for GPU memory. pcl::gpu::DeviceMemory/DeviceMemory2D/DeviceArray&lt;T&gt;/DeviceArray2D&lt;T&gt; (Thrust containers are incinvinient).</p>
<blockquote>
<div><ul class="simple">
<li>DeviceArray2D&lt;T&gt; is container for organized point cloud data (supports row alignment)</li>
</ul>
</div></blockquote>
</li>
<li><p class="first">PointCloud Channels for GPU memory. Say, with “_gpu” postfix.</p>
<blockquote>
<div><ul class="simple">
<li>cloud[“xyz_gpu”] =&gt; gets channel with 3D x,y,z data allocated on GPU.</li>
<li>GPU functions (ex. gpu::computeNormals) create new channel in cloud (ex. “normals_gpu”) and write there. Users can preallocate the channel and data inside it in order to save time on allocations.</li>
<li>Users must manually invoke uploading/downloading data to/from GPU. This provides better understanding how much each operation costs.</li>
</ul>
</div></blockquote>
</li>
<li><p class="first">Two layers in GPU part:  host layer(nvcc-independent interface) and device(for advanced use, for sharing code compiled by nvcc):</p>
<blockquote>
<div><ul class="simple">
<li>namespace pcl::cuda (can depend on CUDA headers) or pcl::gpu (completely independent from CUDA, OpenCL support in future?).</li>
<li>namespace pcl::device for device layer, only headers.</li>
</ul>
</div></blockquote>
</li>
<li><p class="first">Async operation support???</p>
</li>
</ol>
</div></blockquote>
</div>
<div class="section" id="keypoints-and-features">
<h3>1.4 Keypoints and features</h3>
<blockquote>
<div><ol class="arabic">
<li><p class="first">The name Feature is a bit misleading, since it has tons of meanings. Alternatives are Descriptor or FeatureDescription.</p>
</li>
<li><p class="first">In the feature description, there is no need in separate FeatureFromNormals class and setNormals() method, since all the required channels are contained in one input. We still need separate setSearchSurface() though.</p>
</li>
<li><p class="first">There exist different types of keypoints (corners, blobs, regions), so keypoint detector might return some meta-information besides the keypoint locations (scale, orientation etc.). Some channels of that meta-information are required by some descriptors. There are options how to deliver that information from keypoints to descriptor, but it should be easy to pass it if a user doesn’t change anything. This interface should be uniform to allow for switching implementations and automated benchmarking. Still one might want to set, say, custom orientations, different from what detector returned.</p>
<blockquote>
<div><p>to be continued…</p>
</div></blockquote>
</li>
</ol>
</div></blockquote>
</div>
<div class="section" id="data-slices">
<h3>1.5 Data slices</h3>
<p>Anything involving a slice of data should use size_t for indices and not int. E.g the indices of the inliers in RANSAC, the focused points in RANSAC …</p>
</div>
<div class="section" id="ransac">
<h3>1.6 RANSAC</h3>
<blockquote>
<div><ul class="simple">
<li>Renaming the functions and internal variables: everything should be named with _src and _tgt: we have confusing names like indices_ and indices_tgt_ (and no indices_src_), setInputCloud and setInputTarget (duuh, everything is an input, it should be setTarget, setSource), in the code, a sample is named: selection, model_ and samples. getModelCoefficients is confusing with getModel (this one should be getBestSample).</li>
<li>no const-correctness all over, it’s pretty scary: all the get should be const, selectWithinDistance and so on too.</li>
<li>the getModel, getInliers function should not force you to fill a vector: you should just return a const reference to the internal vector: that could allow you to save a useless copy</li>
<li>some private members should be made protected in the sub sac models (like sac_model_registration) so that we can inherit from them.</li>
<li>the SampleConsensusModel should be independent from point clouds so that we can create our own model for whatever library. Then, the one used in the specialize models (like sac_model_registration and so on) should inherit from it and have constructors based on PointClouds like now. Maybe we should name those PclSampleConsensusModel or something (or have SampleConsensusModelBase and keep the naming for SampleConsensusModel).</li>
</ul>
</div></blockquote>
</div>
</div>
<div class="section" id="minor-changes">
<h2>Minor changes</h2>
</div>
<div class="section" id="concepts">
<h2>Concepts</h2>
<p>See <a class="reference external" href="http://dev.pointclouds.org/issues/567">http://dev.pointclouds.org/issues/567</a>.</p>
</div>
</div>
<div class="section" id="references">
<h1>References</h1>
<ul class="simple">
<li><a class="reference external" href="www4.in.tum.de/~blanchet/api-design.pdf">The Little Manual of API Design</a></li>
</ul>
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