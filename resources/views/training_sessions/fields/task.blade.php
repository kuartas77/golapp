<h6>Ejercicio N° {{$task}}</h6>
<section>
   <div class="row">
       <div class="col-md-12">
           <div class="form-group form-group-sm">
                <label for="task_name_{{$task}}">Ejercicio N° {{$task}}</label>
                <input type="hidden" name="task_number[]" value="{{$task}}">
                {{html()->select('task_name[]', $tasks, null)->attributes(['class' => 'form-control form-control-sm','id' => 'task_name_'.$task])->placeholder('Selecciona...')}}
           </div>
       </div>
   </div>

   <div class="row">
       <div class="col-md-4">
           <div class="form-group form-group-sm">
               <label for="general_objective_{{$task}}">Objetivo General </label>
               <span class="bar"></span>
               {{html()->select('general_objective[]', $generals, null)->attributes(['class' => 'form-control form-control-sm','id' => 'general_objective_'.$task])->placeholder('Selecciona...')}}
           </div>
       </div>

       <div class="col-md-4">
           <div class="form-group form-group-sm">
               <label for="specific_goal_{{$task}}">Objetivo Específico </label>
               <span class="bar"></span>
               {{html()->select('specific_goal[]', $specifics, null)->attributes(['class' => 'form-control form-control-sm','id' => 'specific_goal_'.$task])->placeholder('Selecciona...')}}
           </div>
       </div>

       <div class="col-md-4">
           <div class="form-group form-group-sm">
               <span >Contenidos</span>
               <span class="bar"></span>
               {{html()->select('content_one[]', $contents, null)->attributes(['class' => 'form-control form-control-sm','id' => 'content_one_'.$task])->placeholder('Selecciona...')}}

               {{html()->select('content_two[]', $contents, null)->attributes(['class' => 'form-control form-control-sm','id' => 'content_two_'.$task])->placeholder('Selecciona...')}}

               {{html()->select('content_three[]', $contents, null)->attributes(['class' => 'form-control form-control-sm','id' => 'content_three_'.$task])->placeholder('Selecciona...')}}
           </div>
       </div>
   </div>

   <div class="row">
       <div class="col-md-4">
           <div class="form-group form-group-sm">
               <label for="TS_{{$task}}">TS</label>
               <span class="bar"></span>
               {!! html()->text('ts[]', null)->attributes(['class' => 'form-control form-control-sm', 'placeholder' => '9', 'id' => 'TS_'.$task]) !!}
               <small class="form-text text-muted">Tiempo de la Serie. 9 Representado en minutos</small>
           </div>
       </div>
       <div class="col-md-4">
           <div class="form-group form-group-sm">
               <label for="SR_{{$task}}">S/R</label>
               <span class="bar"></span>
               {!! html()->text('sr[]', null)->attributes(['class' => 'form-control form-control-sm', 'placeholder' => '2 (1\'D)', 'id' => 'SR_'.$task]) !!}
               <small class="form-text text-muted">Series / Repeticiones, Ejemplo: 2 Series, 1 minuto de descanso. Representado: "2 (1'D)"</small>
           </div>
       </div>
       <div class="col-md-4">
           <div class="form-group form-group-sm">
               <label for="TT_{{$task}}">TT</label>
               <span class="bar"></span>
               {!! html()->text('tt[]', null)->attributes(['class' => 'form-control form-control-sm', 'placeholder' => '20', 'id' => 'TT_'.$task]) !!}
               <small class="form-text text-muted">Tiempo total. Representado en minutos</small>
           </div>
       </div>
    </div>

   <div class="row">
       <div class="col-md-12">
           <div class="form-group form-group-sm">
               <label for="observations{{$task}}">Observaciones</label>
               <span class="bar"></span>
               {!! html()->textarea('observations[]', null)->attributes(['class' => 'form-control form-control-sm','size'=>'3x5','id' => 'observations'.$task]) !!}
           </div>
       </div>
    </div>
</section>