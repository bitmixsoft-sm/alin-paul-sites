<!-- Your Profile  -->
				
				<div class="your-profile">
					<div class="ui-block-title ui-block-title-small">
						<h6 class="title">{{l("Your Profile")}}</h6>
					</div>
				
					<div id="accordion" role="tablist" aria-multiselectable="true">
						<div class="card">
							<div class="card-header" role="tab" id="headingOne">
								<h6 class="mb-0">
									<a data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
										{{l("Profile Settings")}}
										<svg class="olymp-dropdown-arrow-icon"><use xlink:href="svg-icons/sprites/icons.svg#olymp-dropdown-arrow-icon"></use></svg>
									</a>
								</h6>
							</div>
				
							<div id="collapseOne" class="collapse show" role="tabpanel" aria-labelledby="headingOne">
								<ul class="your-profile-menu">
									<li>
										<a href="/profile-settings">{{l("Personal Information")}}</a>
									</li>
								</ul>
							</div>
						</div>
					</div>
				</div>
				
				<!-- ... end Your Profile  -->