{include file="sections/header.tpl"}

					<div class="row">
						<div class="col-sm-12">
							<div class="panel panel-hovered mb20 panel-default">
								<div class="panel-heading">{$_L['Front_end_title']}</div>
								<div class="panel-body">
									<div class="md-whiteframe-z1 mb20 text-center" style="padding: 15px">
										<div class="col-md-8">
											<form id="site-search" method="post" action="{$_url}frontend/frontend/">
											<div class="input-group">
												<div class="input-group-addon">
													<span class="fa fa-search"></span>
												</div>
												<input type="text" name="name" class="form-control" placeholder="{$_L['Search_by_Title']}...">
												<div class="input-group-btn">
													<button class="btn btn-success">{$_L['Search']}</button>
												</div>
											</div>
											</form>
										</div>
										<div class="col-md-4">
											<a href="{$_url}frontend/add" class="btn btn-primary btn-block waves-effect"><i class="ion ion-android-add"> </i> {$_L['New_Frontend']}</a>
										</div>&nbsp;
									</div>

						<table class="table table-striped table-bordered">
							<thead>
								<tr>
									<th>{$_L['Title_Msg']}</th>
									<th>{$_L['Status']}</th>
									<th>{$_L['Manage']}</th>
								</tr>
							</thead>
							<tbody>
							{foreach $d as $ds}
								<tr>
									<td>{$ds['title_msg']}</td>
									<td>{$ds['status']}</td>
									<td>
										<a href="{$_url}frontend/edit/{$ds['id']}" class="btn btn-warning btn-sm">{$_L['Edit']}</a>
										<a href="{$_url}frontend/delete/{$ds['id']}" id="{$ds['id']}" class="btn btn-danger btn-sm cdelete">{$_L['Delete']}</a>
									</td>
								</tr>
							{/foreach}
							</tbody>
						</table>
						{$paginator['contents']}
								</div>
							</div>
						</div>
					</div>

{include file="sections/footer.tpl"}
