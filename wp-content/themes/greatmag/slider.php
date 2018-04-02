<!--Carousel-->
<style>
				.full-width-carousel{
					width: 100%;
					position: relative;
					background: black;
					height: 0;
					margin-bottom: 160px;
				}

				.full-width-carousel-inner{
					position: absolute;
					top: 0;
					left: 0;
					width: 100%;
					height: 100%;
				}

				.full-width-carousel-dummy{
					position: relative;
					margin: 0 30px;
					height: 100%;
					/*z-index: 14;*/
				}

				.full-width-carousel-item{
					position: absolute;
					width: 100%;
					height: 100%;
					top: 0;
					left: 0;
					display: block;
					overflow: hidden;
					text-decoration: none;
					-ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=40)";
					filter: alpha(opacity=40);
					-moz-opacity: 0.4;
					-khtml-opacity: 0.4;
					opacity: 0.4;
					/*z-index: 12;*/
					-webkit-transition: opacity .4s linear;
					-moz-transition: opacity .4s linear;
					-ms-transition: opacity .4s linear;
					-o-transition: opacity .4s linear;
					transition: opacity .4s linear;
				}

				.full-width-carousel-item-inner{
					width: 100%;
					height: 100%;
				}

				.full-width-carousel-item-inner img{
					width: 100%;
					height: auto;
				}

				.full-width-carousel .current{
					-ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=100)";
					filter: alpha(opacity=100);
					-moz-opacity: 1;
					-khtml-opacity: 1;
					opacity: 1;
					/*z-index: 10;*/
				}

				.a-full-width-carousel-right, .a-full-width-carousel-left, .a-shop-carousel-right{
					width: 24px;
					height: 47px;
					-ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=80)";
					filter: alpha(opacity=80);
					-moz-opacity: 0.8;
					-khtml-opacity: 0.8;
					opacity: 0.8;
					-webkit-transition: opacity .2s linear;
					-moz-transition: opacity .2s linear;
					-ms-transition: opacity .2s linear;
					-o-transition: opacity .2s linear;
					transition: opacity .2s linear;
				}

				.featured-carousel{
					margin-top: 10px;
					padding-bottom: 48%;
				}
				.featured-carousel h2{
					position: flex;
					top: 67%;
					width: 96%;
					left: 2%;
					text-align: center;
					font-weight: normal;
					/*padding: 6px 2px 4px 12px;
					max-width: 75%;
					float: left;*/
					font-family: serif;
					font-size: 31px;
					line-height: 21px;
					color: rgb(247,238,219);
					text-transform: none;
				}

				/*.featured-carousel h2 span, .featured-carousel h3 span{*/
				.featured-carousel-bg{
					background: rgba(0, 0, 0, 0.3);
					position: absolute;
					top: 0;
					left: 0;
					width: 100%;
					height: 100%;
				}
        
        /*
				.featured-carousel h3{
					position: absolute;
					font-size: 36px;
					letter-spacing: 0.1em;
					color: white;
					text-transform: uppercase;
					width: 300px;
					left: 50%;
					text-align: center;
					margin-left: -150px;
					bottom: 40%;
					font-weight: normal;
				}
          */

					.a-news-newer, .a-news-older, .a-news-newer:visited, .a-news-older:visited{
						width: 100px;
						line-height: 16px;
					}
				}

				.releases-carousel > .current{
					display: block;
				}

				.carousel-release{
					width: 216px;
					height: 216px;
					position: absolute;
					top: 72px;
					right: -216px;
					z-index: 1;
					background: #000;
				}

				.carousel-release img{
					width: 100%;
					height: 100%;
				}

					.featured-carousel{
						height: 400px;
						margin-bottom: 27px;
					}

					.full-width-carousel{
						padding-bottom: 0;
					}

					.full-width-carousel-inner{
						max-width: 1130px;
						margin: 0 auto;
						position: static;
						width: auto;
					}

					.full-width-carousel-dummy{
						position: relative;
						margin: 0 220px;
					}

					.full-width-carousel-item-inner{
						width: 690px;
						position: absolute;
						top: 0;
						left: 50%;
						margin-left: -345px;
					}

					.a-full-width-carousel-right, .a-full-width-carousel-left, .a-shop-carousel-right{
						width: 24px;
						height: 47px;
						-ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=80)";
						filter: alpha(opacity=80);
						-moz-opacity: 0.8;
						-khtml-opacity: 0.8;
						opacity: 0.8;
						-webkit-transition: opacity .2s linear;
						-moz-transition: opacity .2s linear;
						-ms-transition: opacity .2s linear;
						-o-transition: opacity .2s linear;
						transition: opacity .2s linear;
					}
					
					.a-full-width-carousel-right, .a-full-width-carousel-left{
						position: absolute;
						top: 50%;
						z-index: 15;
						margin-top: -24px;
					}
					
					.a-full-width-carousel-right:hover, .a-full-width-carousel-left:hover, .a-shop-carousel-right:hover{
						-ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=100)";
						filter: alpha(opacity=100);
						-moz-opacity: 1;
						-khtml-opacity: 1;
						opacity: 1;
					}
					
					.a-full-width-carousel-left{
						background-position: 0 0;
						left: 2px;
					}
					
					.a-full-width-carousel-right{
						background-position: -24px 0;
						right: 2px;
					}
					.fa-chevron-left {
						padding-left: 10px;
					}
					.fa-chevron-right {
						padding-right: 50px;
					}
					a, a:hover, a:active, a:visited, a:focus {
						text-decoration:none;
				}
					.h2-tagline {
							font-size: 11px;
							line-height: 15px;
							width: auto%;
							margin-left: 23%;
							font-family: Poppins;
							letter-spacing: 0.15em;
							color: #191762;
					}
					.hr-header {
							color: #000;
							background-color: #000;
							height: 1px;
							width: 50%;
							border: 0;
							margin-left: 23%;
							display: block;
					}

					hr {
							display: block;
							unicode-bidi: isolate;
							-webkit-margin-before: 0.5em;
							-webkit-margin-after: 0.5em;
							-webkit-margin-start: auto;
							-webkit-margin-end: auto;
							overflow: hidden;
							border-style: inset;
							border-width: 1px;
					}
					@media screen and (min-width: 101px) and (max-width: 550px)
					 {
							.full-width-carousel-inner{
								 display: none; 
								
							}   
							.full-width-carousel {
								background: transparent;
							}
						}
				</style>

<div class="featured-carousel full-width-carousel">
  <div class="full-width-carousel-inner">
    <div class="full-width-carousel-dummy">
            <a href="" class="current full-width-carousel-item" data-i="0">
		        <div class="full-width-carousel-item-inner">
                <img src="http://localhost/BlueQuote/wp-content/uploads/2017/12/25398312_384562478640829_4279158507161862933_o.jpg" alt=""/>
        </div>
        <div class="featured-carousel-bg">
						<h2 class="carousel-caption">Placeholder 1</h2>
					</div>
		      </a>
            <a href="" class="full-width-carousel-item" data-i="1">
		        <div class="full-width-carousel-item-inner">
          <img src="http://localhost/BlueQuote/wp-content/uploads/2018/01/lejla2.jpg" alt=""/>
        </div>
        <div class="featured-carousel-bg">
						<h2 class="carousel-caption">Placeholder 2</h2>
					</div>
		      </a>
            <a href="" class="full-width-carousel-item" data-i="2">
		        <div class="full-width-carousel-item-inner">
          <img src="http://localhost/BlueQuote/wp-content/uploads/2018/01/lejla3.jpg" alt=""/>
        </div>
        <div class="featured-carousel-bg">
						<h2 class="carousel-caption">Placeholder 3</h2>
					</div>
		      </a>
            <a href="" class="full-width-carousel-item" data-i="3">
		        <div class="full-width-carousel-item-inner">
          <img src="http://localhost/BlueQuote/wp-content/uploads/2018/01/lejla4.jpg" alt="" />
        </div>
        <div class="featured-carousel-bg">
						<h2 class="carousel-caption">Placeholder 4</h2>
					</div>
		      </a>
            <a href="" class="full-width-carousel-item featured-video" data-i="4">

		        <div class="full-width-carousel-item-inner">
          <img src="http://localhost/BlueQuote/wp-content/uploads/2018/02/news.jpg" alt=""/>
        </div>
        <div class="featured-carousel-bg">
						<h2 class="carousel-caption">Placeholder 5</h2>

	        		</div>
		      </a>

  </div>
  <a class="a-full-width-carousel-left fa fa-chevron-left fa-3x" href="#"></a>
  <a class="a-full-width-carousel-right ir fa fa-chevron-right fa-4x" href="#"></a>
</div>
<!--Carousel end-->

	<!--Scripts-->
	<script defer src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
  <script defer src="https://cdnjs.cloudflare.com/ajax/libs/hammer.js/2.0.8/hammer.min.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/jquery-hammerjs@2.0.0/jquery.hammer.min.js"></script>
	<script defer src="http://localhost/BlueQuote/wp-content/themes/greatmag/js/slider.js">
	</script> 
	<!--Scripts end-->
</div>		

