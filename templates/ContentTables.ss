<% if $showItems %>
	<% loop $showItems %>
		<% loop $Me %>
			<% if $Pos == 2 %>
				<% loop $Me %>
					<% if $Pos == 2 %>
						<h2 id="$item">$item</h2>
					<% end_if %>
				<% end_loop %>
			<% end_if %>
		<% end_loop %>
		<table class="table table-bordered table-striped">
			<tbody>
				<% loop $Me %>
					<tr>
						<% if $Me %>
							<% loop $Me %>
								<% if $Up.Pos == 1 %>
									<th>$item</th>
								<% else %>
									<td>$item</td>
								<% end_if %>
							<% end_loop %>
						<% end_if %>
					</tr>
				<% end_loop %>
			</tbody>
		</table>
	<% end_loop %>
<% end_if %>