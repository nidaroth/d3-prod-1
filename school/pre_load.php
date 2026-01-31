<div class="preloader">
	<div class="loader">
		<div class="loader__figure"></div>
		<p class="loader__label">Diamond SIS</p>
	</div>
</div>


<!-- DIAM-589 -->
<div class="preloader_grid" style="display: none;">
	<div class="loader_grid">
		<div class="loader__figure_grid"></div>
		<p class="loader__label_grid">Please Wait.....!</p>
	</div>
</div>
<style>
	

	.preloader_grid {
		width: 100%;
		height: 100%;
		top: 0px;
		position: fixed;
		z-index: 99999;
		background: #ffffffa6;
	}

	.loader_grid {
		overflow: visible;
		padding-top: 2em;
		height: 0;
		width: 2em;
	}

	.loader__figure_grid {
		height: 0;
		width: 0;
		box-sizing: border-box;
		border: 0 solid #1976d2;
		border-radius: 50%;
		animation: loader-figure 1.15s infinite cubic-bezier(0.215, 0.61, 0.355, 1);
	}

	.loader__label_grid {
		float: left;
		transform: translateX(-50%);
		margin: 0.5em 0 0 50%;
		font-size: 0.875em;
		letter-spacing: 0.1em;
		line-height: 1.5em;
		color: #1976d2;
		white-space: nowrap;
		animation: loader-label 1.15s infinite cubic-bezier(0.215, 0.61, 0.355, 1);
	}

	.loader_grid,
	.loader__figure_grid {
		position: absolute;
		top: 50%;
		left: 50%;
		transform: translate(-50%, -50%);
	}
</style>
<!-- End DIAM-589 -->