@extends ('layouts.admin-default')

@section ('page_title', 'Seating Plans - ' . $event->display_name)

@section ('content')

<div class="row">
	<div class="col-lg-12">
		<h1 class="page-header">Seating Plans - {{ $seating_plan->name }}</h1>
		<ol class="breadcrumb">
			<li>
				<a href="/admin/events/">Events</a>
			</li>
			<li>
				<a href="/admin/events/{{ $event->slug }}">{{ $event->display_name }}</a> 
			</li>
			<li>
				<a href="/admin/events/{{ $event->slug }}/seating">Seating Plans</a>
			</li>
			<li class="active">
				{{ $seating_plan->name }}
			</li>
		</ol>
	</div>
</div>

@include ('layouts._partials._admin._event.dashMini')

<div class="row">
	<div class="col-lg-8">
		
		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-desktop fa-fw"></i> Seating Plan Preview
			</div>
			<div class="panel-body">
				<div class="table-responsive">
					<table class="table">
						@for ($column = 1; $column <= $seating_plan->columns; $column++)
							<?php
								$headers = explode(',', $seating_plan->headers);
								$headers = array_combine(range(1, count($headers)), $headers);
							?>
							<tr>
								<td>
									<h4><strong>ROW {{ucwords($headers[$column])}}</strong></h4>
								</td>
								@for ($row = 1; $row <= $seating_plan->rows; $row++)
									<td style="padding-top:14px;">
										@if($event->getSeat($seating_plan->id, ucwords($headers[$column]) . $row))
											@foreach($seating_plan->seats as $seat)
												<?php
													if($seat->seat == (ucwords($headers[$column]) . $row)){
														$username = $seat->eventParticipant->user->steamname;
														$participant_id = $seat->eventParticipant->id;
													}
												?>
											@endforeach
											<button class="btn btn-success btn-sm"  onclick="editSeating('{{ ucwords($headers[$column]) . $row }}', '{{ $username }}', '{{ $participant_id }}')" data-toggle="modal" data-target="#editSeatingModal">
												{{ ucwords($headers[$column]) . $row }} - {{ $username }}
											</button>
										@else
											<button class="btn btn-primary btn-sm"  onclick="editSeating('{{ ucwords($headers[$column]) . $row }}')" data-toggle="modal" data-target="#editSeatingModal">
												{{ ucwords($headers[$column]) . $row }} - Empty
											</button>
										@endif
									</td>
								@endfor
							</tr>
						@endfor
					</table>
				</div>
			</div>
		</div>
		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-th fa-fw"></i> Seating Plan
			</div>
			<div class="panel-body">
			 <div class="dataTable_wrapper">
					<table width="100%" class="table table-striped table-hover" id="seating_table">
						<thead>
							<tr>
								<th></th>
								<th>Seat Number</th>
								<th>Name</th>
								<th>Steam Name</th>
								<th>Ticket ID</th>
								<th>Purchase ID</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							@foreach ($seating_plan->seats as $seat)
								<tr class="odd gradeX">
									<td></td>
									<td>{{ ucwords($seat->seat) }}</td>
									<td>{{ $seat->eventParticipant->user->username }}</td>
									<td>{{ $seat->eventParticipant->user->steamname }}</td>
									<td>
										@if(empty($seat->eventParticipant->ticket_id))
											Free
										@else
											{{ $seat->eventParticipant->ticket_id }}
										@endif
									</td>
									<td>
										@if(empty($seat->eventParticipant->purchase_id))
											Free
										@else
											{{ $seat->eventParticipant->purchase_id }}
										@endif
									</td>
									<td width="10%">
										<button type="button" class="btn btn-primary btn-sm btn-block" onclick="editSeating('{{ ucwords($seat->seat) }}', '{{ $seat->eventParticipant->user->username }}', '{{ $seat->eventParticipant->id }}')" data-toggle="modal" data-target="#editSeatingModal">Edit</button>
									</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
		</div>
	
	</div>
	<div class="col-lg-4">

		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-wrench fa-fw"></i> Settings
			</div>
			<div class="panel-body">
				{{ Form::open(array('url'=>'/admin/events/' . $event->slug . '/seating/' . $seating_plan->slug, 'files' => 'true')) }}
					@if ($errors->any())
					  	<div class="alert alert-danger">
					        <ul>
					          	@foreach ($errors->all() as $error)
					            	<li>{{ $error }}</li>
					          	@endforeach
					        </ul>
					  	</div>
					@endif
					<div class="row">
						<div class="col-lg-6 col-sm-12 form-group">
							{{ Form::label('name','Name',array('id'=>'','class'=>'')) }}
							{{ Form::text('name', $seating_plan->name ,array('id'=>'name','class'=>'form-control')) }}
						</div>
						<div class="col-lg-6 col-sm-12 form-group">
								{{ Form::label('event_status','Status',array('id'=>'','class'=>'')) }}
								{{ 
									Form::select(
										'status',
										array(
											'draft'=>'Draft',
											'published'=>'Published',
										),
										strtolower($seating_plan->status),
										array(
											'id'=>'status',
											'class'=>'form-control'
										)
									)
								}}
						</div>
					</div>
					<div class="row">
						<div class="col-lg-6 col-sm-12 form-group">
							{{ Form::label('columns','Columns',array('id'=>'','class'=>'')) }}
							{{ Form::text('columns', $seating_plan->columns ,array('id'=>'columns','class'=>'form-control')) }}
						</div> 
						<div class="col-lg-6 col-sm-12 form-group">
							{{ Form::label('rows','Rows',array('id'=>'','class'=>'')) }}
							{{ Form::text('rows', $seating_plan->rows ,array('id'=>'rows','class'=>'form-control')) }}
						</div>
					</div>
					<div class="form-group">
						{{ Form::label('image','Seating Plan Image',array('id'=>'','class'=>'')) }}
						{{ Form::file('image',array('id'=>'image','class'=>'form-control')) }}
					</div>
					<div class="form-group"> 
						<div class="checkbox"> 
							<label> 
								@if ($seating_plan->locked) 
									{{ Form::checkbox('locked', 1, true)}} Lock Seating 
								@else 
									{{ Form::checkbox('locked', 1)}} Lock Seating 
								@endif 
							</label> 
						</div> 
					</div>
					<button type="submit" class="btn btn-default">Submit</button>
					@if ($seating_plan->image_path)
						<hr>
						<h4>Image Preview</h4>
						<img src="{{ $seating_plan->image_path }}" class="img img-responsive" />
					@endif
				{{ Form::close() }}
			</div>
		</div>
	
	</div>
</div>

<!-- Modals -->
<div class="modal fade" id="editSeatingModal" tabindex="-1" role="dialog" aria-labelledby="editSeatingModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title" id="editSeatingModalLabel">Edit Seating</h4>
			</div>
			<div class="modal-body">
				{{ Form::open(array('url'=>'/admin/events/' . $event->slug . '/seating/' . $seating_plan->slug . '/seat')) }}
					<div class="form-group">
						{{ Form::label('seat_number_modal','Seat Number',array('id'=>'','class'=>'')) }}
						{{ Form::text('seat_number_modal', null, array('id'=>'seat_number_modal','class'=>'form-control')) }}
					</div>
					<div class="form-group">
						{{ Form::label('participant_select_modal','Participant',array('id'=>'','class'=>'')) }}
						{{ Form::select('participant_select_modal', $event->getParticipants(), null, array('id'=>'participant_select_modal','class'=>'form-control')) }}
					</div> 
					{{ Form::hidden('participant_id_modal', null, array('id'=>'participant_id_modal','class'=>'form-control')) }}
					{{ Form::hidden('event_id_modal', null, array('id'=>'event_id_modal','class'=>'form-control')) }}

					<a href="" id="participant_link">
						<button type="button" class="btn btn-default">Go to Participant</button>
					</a>
					<button type="submit" class="btn btn-default">Save Changes</button>
				{{ Form::close() }}
				{{ Form::open(array('url'=>'/admin/events/' . $event->slug . '/seating/' . $seating_plan->slug . '/seat', 'id'=>'clear_seat_form')) }}
					<hr>
					{{ Form::hidden('_method', 'DELETE') }}
					{{ Form::hidden('seat_number', null, array('id'=>'seat_number')) }}
					<button type="submit" class="btn btn-danger">Clear Seat</button>
				{{ Form::close() }}
			</div>
		</div>
	</div>
</div>

<!-- JavaScript-->
<script>
	function editSeating(seat, username = null, participant_id = null)
	{
		seat = seat.trim();
		$("#seat_number_modal").val(seat);
		$("#seat_number").val(seat);
		var orginal_participant_id = $("#participant_id_modal").val();
		//Reset all inputs
		$("#seat_number_modal").prop('readonly', '');
		$("#participant_link").prop('href', '');
		$("#participant_link").hide();
		$("#clear_seat_form").hide();
		$("#participant_select_modal").val('');
		$("#participant_id_modal").val('');
		$('#participant_select_modal option[value="' + orginal_participant_id + '"]').attr("selected",false);
		if(username != null){
			//we have a user - Fill in extra
			$("#seat_number_modal").prop('readonly', 'readonly');
			$("#participant_link").prop('href', '/admin/events/{{ $event->slug }}/participants/' + participant_id);
			$("#participant_link").show();
			$("#clear_seat_form").show();
			$('#participant_select_modal').val(participant_id);
		}
	}
</script>

@endsection
