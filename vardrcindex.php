<? require_once("global/config.php"); ?>
<!DOCTYPE html>
<html lang="en">

<head>
	<title>Home | <?=$title?></title>
	
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
   <? require_once("css.php"); ?>
</head>

<body class="horizontal-nav skin-default card-no-border left-abs-menu-page">
    <? require_once("loader.php"); ?>
    <section id="wrapper">
        <? require_once("menu.php"); ?>
        
        <div class="position-relative" style="padding-bottom: 250px;">
            <div class="left-abs-menu">
                <h3>Solutions for Colleges</h3>
                <a class="btn btn-lg btn-theme" href="#" target="_blank">
                    <span>
                    Watch a Demo
                    </span>
                </a>
                <ul class="no-list-h px-0 mt-30 left-side-bar">
                    <li><a href="overview.php">Overview</a></li>
                    <li>Diamond Student Info Software
                        <ul>
                            <li>Collaboration</li>
                            <li>Admissions</li>
                            <li>Registrar</li>
                            <li>Finance</li>
                            <li>Accounting</li>
                            <li>Career Services</li>
                            <li>Compliance Reporting</li>
                            <li>Management Information Reporting </li>
                        </ul>
                    </li>
                    <li>Diamond Admissions Document Manager
                        <ul>
                            <li>Virtual Enrollment</li>
                            <li>In-Person Enrollment</li>
                            <li>Centralized Document Repository</li>
                        </ul>
                    </li>
                    <!-- <li>Enroll</li> -->
                    <li>Diamond Pay
                        <ul>
                            <li>Student Payment System</li>
                        </ul>
                    </li>
                    <li>Student Portal</li>
                    <li>Instructor Portal</li>
                    
                    <li>API—3<sup>rd</sup> Party Integrations
                        <ul>
                            <li>Partners</li>
                        </ul>
                    </li>
                </ul>
            </div>
            <div class="card home-banner" style="min-height: 550px;">
                <div class="bg-image"></div>
                <div class="card-body">
                    <!-- <h4>An All-in-One Workspace for College Adminstration</h4> -->
                    <ul class="business-logics" style="">
                        <li>Student Information System/Software?</li>
                        <li>Student Management Software?</li>
                        <li>Campus Management Software?
                            <ul>
                                <li>Whatever you call it, you’ve come to the right place.</li>
                            </ul>
                        </li>

                    </ul>
                    <h2>Easy to use, easy to adopt and very easy to buy.</h2>
                    <div class="d-flex">
                        <!-- <a class="btn btn-lg btn-theme" href="#" target="_blank">
                            <span>
                            Watch Demos
                            </span>
                        </a> -->
                        <a class="btn btn-lg btn-success mt-20" href="signup-free-trial">
                            <span>
                            Try For Free
                            </span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="diamond-help">
                <div class="card-body">
                    <h1>Work better--together</h1>
                    <p>Work better, together.  Diamond is more than just student information software—it’s a better way to work.  Teams that use Diamond are more productive and better organized.  They collaborate and communicate better whether they are on-campus or working remotely.  And, they are far more efficient than before.  <a href="tools-for-your-team.php">Learn more</a>. 
                    </p>
                </div>
            </div>
        </div>
    </section>
    <? require_once("footer.php"); ?>
    <? require_once("js.php"); ?>
</body>
</html>