<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>A Table NYC</title>
		<?php include('headerfiles.inc.php'); ?>
	</head>
	<body id="home">
		<div id="outer-wrap">
			<div id="page-wrap">
		<?php include('header.inc.php'); ?>
				<div id="page-content"  >
					<div id="home-wrap">
						<div id="menu" class="rounded-corners">
							<form>
								<h2>This Week's Menu</h2>
								<div class="row"><input id="datepicker" type="text" value="Select Delivery Date"></div>
								<div class="row">
									<label>Select Quantity</label>
									<div class="qty-wrap">
										<input type="button" value="+" id="add1" class="add" />
										<input type="text" length="3" value="1" class="quantity qty" id="qty">
										<input type="button" value="-" id="minus1" class="minus" />
									</div>
								</div>
								<h3 class="rounded-corners">Appetizer</h3>
								<div class="row option">Sample Option <a href="/wireframe/index/ajax.htm?width=375" class="optpop rounded-corners jTip" id="one" >i</a></div>
								<h3 class="rounded-corners">Side Dish</h3>
								<div class="row option">Sample Option <a href="/wireframe/index/ajax.htm?width=375" class="optpop rounded-corners jTip" id="two" >i</a></div>
								<h3 class="rounded-corners">Entree</h3>
								<div class="row option">Sample Option <a  href="/wireframe/index/ajax.htm?width=375"  class="optpop rounded-corners jTip" id="three">i</a></div>
								<div class="divider"></div>
								<h2>Kid's Menu</h2>
								<div class="row">
									<label>Select Quantity</label>
									<div class="qty-wrap">
										<input type="button" value="+" id="add1" class="add" />
										<input type="text" length="3" value="0" class="quantity qty" id="qty">
										<input type="button" value="-" id="minus1" class="minus" />
									</div>
									<h3 class="rounded-corners">Entree</h3>
									<div class="row option">Sample Option <a  href="/wireframe/index/ajax.htm?width=375"  class="optpop rounded-corners jTip" id="four">i</a></div>
									<div class="row button-row">
										<input type="submit" value="Order Now" class="order-now">
									</div>
							</form>
						</div>
					</div>
						<!-- End Menu Box -->
					<div class="home-intro">
						<div class="slideshow">
							<div class="slide">
								<img src="images/home-photo.jpg">
								<a href="" class="custom-button">Create a Custom Menu</a>
							</div>
							<div class="slide">
								<img src="images/home-photo3.jpg">
								<a href="" class="custom-button">Create a Custom Menu</a>
							</div>
							<div class="slide">
								<img src="images/home-photo2.jpg">
								<a href="" class="custom-button">Create a Custom Menu</a>
							</div>
						</div>
						<div class="intro-text">
							<p><strong>Lorem ipsum dolor sit amet</strong>, consectetur adipiscing elit. Cras ut nisl non dolor faucibus accumsan. Phasellus scelerisque suscipit dolor, et tincidunt nunc ultrices ut. Sed non risus arcu, at tincidunt felis. Aliquam erat volutpat. Morbi non mi sit amet urna scelerisque mollis.
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php include('footer.inc.php'); ?>
		</div>
	</body>
</html>
