@extends('template/template')

@section('content')
  @if($showExport)
    {{HTML::script('/js/dataTables.tableTools.min.js')}}
    {{HTML::style('/css/dataTables.tableTools.min.css')}}
  @endif
	<script>
		$(document).ready(function(){
			var oTable = $('.tablaCatalogo').dataTable({
				"processing" : true,
				"serverSide" : true,
				"ajax" : "{{Request::path()}}/0",
				"bLengthChange": false,
				"sDom": '<"top"<"col-md-5 col-titulo"><"col-md-4"f><"col-md-3 col-boton-agregar text-right">><"col-md-12"rt><"bottom"<"col-md-6"i><"col-md-6"p>><"clear">',
				"iDisplayLength": {{$perPage}},
				"columnDefs": [{
			    "targets": -1,
			    "class": "text-right",
			    "data": null,
			    "sortable": false,
			    "render": function ( data, type, full, meta ) {
			    	var col = data.length-1;
			    	var id = data[col];	 
			    	var html = '';
			    	@if($permisos['edit'])   	
							html = '<a class="btn btn-xs btn-primary" title="Editar" href="{{ URL::to(Request::url())}}/' + id + '/edit"><span class="glyphicon glyphicon-pencil"></span></a>&nbsp;';
						@endif;
						@if($permisos['delete'])
							html += '<form action="{{ URL::to(Request::url())}}/' + id + '" class="btn-delete" method="POST">\
								<input type="hidden" name="_method" value="DELETE">\
								<button type="submit" class="btn btn-xs btn-danger" title="Borrar" onclick="return confirm(\'¿Está seguro que desea eliminar este registro?\')">\
								<i class="glyphicon glyphicon-trash"></i>\
								</button>\
								</form>';
						@endif;
			      return html;
			    }
			  }, 
			  <?php $i=0; ?>
			  @foreach ($columnas as $columna)
				  @if(($columna["tipo"]=="date") || ($columna["tipo"]=="datetime"))
					  {
					  	"targets" : {{$i}}, "data" : null,
					  	"class"   : "{{$columna["class"]}}",
					  	"render" : function(data) {
					  		var fecha = data[{{$i}}];
					  		var arrhf = fecha.split(" "); 
					  		var arrf  = arrhf[0].split("-");
					  		var hora  = '';
					  		if (arrhf.length==2) {hora = ' ' + arrhf[1];}
					  		return arrf[2] + '-' + arrf[1] + '-' + arrf[0] + hora;
					  	}
					  },
					@elseif ($columna["tipo"]=="numeric")
					  {
					  	"targets" : {{$i}}, "data" : null,
					  	"class"   : "{{$columna["class"]}}",
					  	"render" : function(data) {
					  		var val = data[{{$i}}];
					  		if (val==null) return null;

					  		val = Number(val);
					  		return val.formatMoney({{$columna["decimales"]}});
					  	}
					  },
			  	@elseif($columna["tipo"]=="bool")
					  {
					  	"targets" : {{$i}}, "data" : null,
					  	"class"   : "{{$columna["class"]}}",
					  	"render" : function(data) {
					  		var val = data[{{$i}}];
								if (val==null) return null;

								var text = (val==0?'<span class="label label-default" style="display:block; width: 40px; margin: auto;">No</span>':'<span class="label label-success" style="display:block; width: 40px; margin:auto;">Si</span>');
					  		return text;
					  	}
					  },
			  	@endif
			  	<?php $i++; ?>
			  @endforeach
			  ],

				"oLanguage": {
     			"sLengthMenu": "Mostrar _MENU_ resultados por p&aacute;gina",
          "sZeroRecords": "No se encontraron registros",
          "sInfo": "Mostrando _START_ a _END_ de _TOTAL_ resultados",
          "sInfoEmpty": "Mostrando 0 a 0 de 0 resultados",
          "sInfoFiltered": "(filtrado de _MAX_ resultados totales)",
					"sSearch":"",
					"sProcessing":"Procesando",
					"oPaginate": {
						"sPrevious":"Anterior",
						"sNext":"Siguiente",
						"sFirst":"Primera",
						"sLast":"Ultima"
					}
				}
			});
			@if((!$permisos['edit'])&&(!$permisos['edit']))   	   
				oTable.fnSetColumnVis(-1,false);
			@endif;

      $('.tablaCatalogo').each(function(){

        var txSearch = $(this).closest('.dataTables_wrapper').find('div[id$=_filter] input');
        txSearch.attr('placeholder', 'Buscar').addClass('form-control input-md').removeClass('input-sm').css('width', '100%');

        var txSearchLabel = $(this).closest('.dataTables_wrapper').find('div[id$=_filter] label');
			 	txSearchLabel.css('width', '100%').css('margin-bottom','0');
				
				var txInfo = $(this).closest('.dataTables_wrapper').find('div[id$=_info]');
				txInfo.addClass('small text-muted');

				var divBoton = $(this).closest('.dataTables_wrapper').find('.col-boton-agregar');
				@if($permisos['add'])
			 		divBoton.html('<a class="btn btn-success" href="{{ URL::to(Request::url() . '/create') }}">\
						<span class="glyphicon glyphicon-plus"></span>&nbsp;Agregar</a>');
			 	@else
					divBoton.html('<a></a>');
				@endif

      });

      @if($showExport)
	      var tableTools = new $.fn.dataTable.TableTools(oTable, {
	          "sSwfPath": "/swf/copy_csv_xls_pdf.swf",
	          "aButtons": [{
	            "sExtends": "xls",
	            "sButtonText": "Excel",
	            "sButtonClass": "btn btn-default btn-export",
	            "oSelectorOpts": {
	                "page": "all"
	            }
	          }]
	      });
	      $(tableTools.fnContainer()).insertBefore('.col-boton-agregar a');
	      $('.btn-export').removeClass('DTTT_button');
	      $('.DTTT_container').css('margin-bottom','0').css('margin-left','4px');
	      $('.DTTT_container .btn').prepend('<span class="glyphicon glyphicon-save"></span>&nbsp;');
      @endif

		});

		Number.prototype.formatMoney = function(aDec) {
      var n = this,
      sign = n < 0 ? "-" : "",
      i = parseInt(n = Math.abs(+n || 0).toFixed(aDec)) + "",
      j = (j = i.length) > 3 ? j % 3 : 0;
        return sign + (j ? i.substr(0, j) + "," : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + ",") 
          + (aDec ? "." + Math.abs(n - i).toFixed(aDec).slice(2) : "");
    };
	</script>

	@if(Session::get('message'))
		<div class="alert alert-{{ Session::get('type') }} alert-dismissable .mrgn-top">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
			{{ Session::get('message') }}
		</div>
	@endif
	<table class="table table-striped table-bordered table-condensed tablaCatalogo display">
		<thead>
      <tr>
      	@foreach ($columnas as $columna) 
        	<th>{{$columna["nombre"]}}</th>
        @endforeach
        <th>&nbsp;</th>
      </tr>
    </thead>
 
	</table>
@stop