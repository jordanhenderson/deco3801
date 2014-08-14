<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>Review - PCR</title>

	<!-- Bootstrap Core CSS -->
	<link href="css/bootstrap.min.css" rel="stylesheet">

	<!-- Custom CSS -->
	<link href="css/main.css" rel="stylesheet">

	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
		<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
	<![endif]-->
</head>

<body>
	<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
		<div class="container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="#">Peer Code Review</a>
			</div>
			<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
				<ul class="nav navbar-nav">
					<li><a href="index.php">Dashboard</a></li>
					<li><a href="#">Help Centre</a></li>
					<li><a href="admin.php">Admin</a></li>
				</ul>
			</div>
		</div>
	</nav>
	<div class="container">
		<div class="col-xs-6 col-sm-3 sidebar-offcanvas" id="sidebar">
			<div class="list-group">
				<a href="#" class="list-group-item active">main.c</a>
				<a href="#" class="list-group-item">shared.c</a>
				<a href="#" class="list-group-item">temp.c</a>
				<a href="#" class="list-group-item">main.h</a>
				<a href="#" class="list-group-item">shared.h</a>
				<a href="#" class="list-group-item">globals.h</a>
			</div>
		</div>
		<div class="col-md-9">
			<h1>Assignment 99 Submission</h1>
			<div class="col-md-12">
				<h2>main.c</h2>
				<pre>struct group_info init_groups = { .usage = ATOMIC_INIT(2) };
struct group_info *groups_alloc(int gidsetsize){
    struct group_info *group_info;
        int nblocks;
        int i;
        
    nblocks = (gidsetsize + NGROUPS_PER_BLOCK - 1) / NGROUPS_PER_BLOCK;
        /* Make sure we always allocate at least one indirect block pointer */
        nblocks = nblocks ? : 1;
        group_info = kmalloc(sizeof(*group_info) + nblocks*sizeof(gid_t *), GFP_USER);
        if (!group_info)
            return NULL;
            group_info->ngroups = gidsetsize;
        group_info->nblocks = nblocks;
        atomic_set(&group_info->usage, 1);
        
    if (gidsetsize <= NGROUPS_SMALL)
            group_info->blocks[0] = group_info->small_block;
            else {
            for (i = 0; i < nblocks; i++) {
                    gid_t *b;
                        b = (void *)__get_free_page(GFP_USER);
                        if (!b)
                            goto out_undo_partial_alloc;
                            group_info->blocks[i] = b;
                    }
            }
        return group_info;
        
out_undo_partial_alloc:
    while (--i >= 0) {
            free_page((unsigned long)group_info->blocks[i]);
            }
        kfree(group_info);
        return NULL;
        }
    
EXPORT_SYMBOL(groups_alloc);
				</pre>
				<p>
					<a class="btn btn-primary" href="#" role="button">Submit</a>
					<a class="btn btn-info" href="#" role="button">Save</a>
					<a class="btn btn-warning" href="#" role="button">Reset</a>
				</p>
			</div>
		</div>
	</div>
	
	<!-- jQuery Version 1.11.0 -->
	<script src="js/jquery-1.11.0.js"></script>

	<!-- Bootstrap Core JavaScript -->
	<script src="js/bootstrap.min.js"></script>
</body>
</html>