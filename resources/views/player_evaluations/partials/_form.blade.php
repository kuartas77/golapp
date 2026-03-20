@csrf
@if(isset($playerEvaluation))
@method('PUT')
@endif

<input type="hidden" name="inscription_id" value="{{ old('inscription_id', $inscription->id) }}">
<input type="hidden" name="evaluation_period_id" value="{{ old('evaluation_period_id', $period->id) }}">
<input type="hidden" name="evaluation_template_id" value="{{ old('evaluation_template_id', $template->id) }}">

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <label class="font-weight-bold">Jugador</label>
                <div>
                    {{ $inscription->player?->full_names ?? $inscription->player?->full_name ?? $inscription->player?->name ?? '—' }}
                </div>
            </div>

            <div class="col-md-3">
                <label class="font-weight-bold">Grupo</label>
                <div>{{ $inscription->trainingGroup?->name ?? '—' }}</div>
            </div>

            <div class="col-md-3">
                <label class="font-weight-bold">Período</label>
                <div>{{ $period->name }}</div>
            </div>

            <div class="col-md-3">
                <label class="font-weight-bold">Plantilla</label>
                <div>{{ $template->name }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="form-row">
            <div class="form-group col-md-4">
                <label>Tipo de evaluación</label>
                @php
                $evaluationType = old('evaluation_type', $playerEvaluation->evaluation_type ?? 'periodic');
                @endphp
                <select name="evaluation_type" class="form-control form-control-sm" required>
                    <option value="initial" {{ $evaluationType === 'initial' ? 'selected' : '' }}>Inicial</option>
                    <option value="periodic" {{ $evaluationType === 'periodic' ? 'selected' : '' }}>Periódica</option>
                    <option value="final" {{ $evaluationType === 'final' ? 'selected' : '' }}>Final</option>
                    <option value="special" {{ $evaluationType === 'special' ? 'selected' : '' }}>Especial</option>
                </select>
            </div>

            <div class="form-group col-md-4">
                <label>Estado</label>
                @php
                $status = old('status', $playerEvaluation->status ?? 'draft');
                @endphp
                <select name="status" class="form-control form-control-sm" required>
                    <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Borrador</option>
                    <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completada</option>
                    <option value="closed" {{ $status === 'closed' ? 'selected' : '' }}>Cerrada</option>
                </select>
            </div>

            <div class="form-group col-md-4">
                <label>Fecha evaluación</label>
                <input
                    type="datetime-local"
                    name="evaluated_at"
                    class="form-control form-control-sm"
                    value="{{ old('evaluated_at', isset($playerEvaluation->evaluated_at) ? \Carbon\Carbon::parse($playerEvaluation->evaluated_at)->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}">
            </div>
        </div>
    </div>
</div>

<div class="row">
    @foreach($criteriaByDimension as $dimension => $criteria)
    <div class="card shadow-sm mb-2 dimension-card col-lg-6 col-md-12 col-sm-12">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">{{ $dimension }}</h5>
                <small class="text-muted">Criterios asociados a esta dimensión</small>
            </div>
            <span class="badge badge-primary p-2">
                Promedio dimensión: <span class="dimension-average">0.00</span>
            </span>
        </div>

        <div class="card-body">
            @foreach($criteria as $criterion)
            @php
            $saved = $existingScores[$criterion->id] ?? null;
            $scoreValue = old("scores.{$criterion->id}.score", $saved['score'] ?? '');
            $scaleValue = old("scores.{$criterion->id}.scale_value", $saved['scale_value'] ?? '');
            $commentValue = old("scores.{$criterion->id}.comment", $saved['comment'] ?? '');
            @endphp

            <div class="border rounded p-3 mb-3 criterion-row"
                data-score-type="{{ $criterion->score_type }}"
                data-min="{{ $criterion->min_score ?? 0 }}"
                data-max="{{ $criterion->max_score ?? 5 }}"
                data-weight="{{ $criterion->weight ?? 1 }}">
                <div class="row">
                    <div class="col-md-4">
                        <label class="font-weight-bold mb-1">{{ $criterion->name }}</label>

                        @if($criterion->code)
                        <div>
                            <small class="text-muted">Código: {{ $criterion->code }}</small>
                        </div>
                        @endif

                        @if($criterion->description)
                        <div>
                            <small class="text-muted">{{ $criterion->description }}</small>
                        </div>
                        @endif

                        <div>
                            <small class="text-muted">
                                Tipo: {{ $criterion->score_type }}
                                @if($criterion->score_type === 'numeric')
                                | Rango:
                                {{ $criterion->min_score !== null ? $criterion->min_score : 0 }}
                                -
                                {{ $criterion->max_score !== null ? $criterion->max_score : 5 }}
                                @endif
                                | Peso: {{ $criterion->weight ?? 1 }}
                                @if($criterion->is_required)
                                | Obligatorio
                                @endif
                            </small>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <input
                            type="hidden"
                            name="scores[{{ $criterion->id }}][template_criterion_id]"
                            value="{{ $criterion->id }}">

                        @if($criterion->score_type === 'numeric')
                        <label>Puntaje</label>
                        <input
                            type="number"
                            step="0.01"
                            min="{{ $criterion->min_score ?? 0 }}"
                            max="{{ $criterion->max_score ?? 5 }}"
                            name="scores[{{ $criterion->id }}][score]"
                            class="form-control score-input"
                            value="{{ $scoreValue }}">

                        <input
                            type="hidden"
                            name="scores[{{ $criterion->id }}][scale_value]"
                            value="">
                        @else
                        @php
                        $options = config('evaluations.scale_options.' . $criterion->score_type, []);
                        @endphp

                        <label>Selección</label>
                        <select
                            name="scores[{{ $criterion->id }}][scale_value]"
                            class="form-control scale-select">
                            <option value="">Seleccione</option>
                            @foreach($options as $value => $label)
                            <option value="{{ $value }}" {{ (string) $scaleValue === (string) $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>

                        <input
                            type="hidden"
                            name="scores[{{ $criterion->id }}][score]"
                            value="">
                        @endif
                    </div>

                    <div class="col-md-2">
                        <label>Valor guardado</label>
                        <input
                            type="text"
                            class="form-control"
                            readonly
                            value="{{ $criterion->score_type === 'numeric' ? ($scoreValue ?: '') : ($scaleValue ?: '') }}">
                    </div>

                    <div class="col-md-4">
                        <label>Comentario</label>
                        <input
                            type="text"
                            name="scores[{{ $criterion->id }}][comment]"
                            class="form-control"
                            value="{{ $commentValue }}"
                            placeholder="Observación del criterio">
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">Resumen general</h5>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-4">
                <label class="font-weight-bold">Preview promedio</label>
                <input type="text" id="overall_preview" class="form-control form-control-sm" readonly value="0.00">
            </div>
            <div class="col-md-8">
                <label class="font-weight-bold d-block">Progreso</label>
                <div class="progress" style="height: 24px;">
                    <div id="overall_bar" class="progress-bar bg-success" style="width: 0%;">0%</div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label>Comentario general</label>
            <textarea name="general_comment" class="form-control form-control-sm" rows="3">{{ old('general_comment', $playerEvaluation->general_comment ?? '') }}</textarea>
        </div>

        <div class="form-group">
            <label>Fortalezas</label>
            <textarea name="strengths" class="form-control form-control-sm" rows="3">{{ old('strengths', $playerEvaluation->strengths ?? '') }}</textarea>
        </div>

        <div class="form-group">
            <label>Oportunidades de mejora</label>
            <textarea name="improvement_opportunities" class="form-control form-control-sm" rows="3">{{ old('improvement_opportunities', $playerEvaluation->improvement_opportunities ?? '') }}</textarea>
        </div>

        <div class="form-group mb-0">
            <label>Recomendaciones</label>
            <textarea name="recommendations" class="form-control form-control-sm" rows="3">{{ old('recommendations', $playerEvaluation->recommendations ?? '') }}</textarea>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end">
    <a href="{{ route('player-evaluations.index') }}" class="btn btn-light mr-2">Cancelar</a>
    <button type="submit" class="btn btn-primary">
        {{ isset($playerEvaluation) ? 'Actualizar evaluación' : 'Guardar evaluación' }}
    </button>
</div>

@push('scripts')
<script>
    $(function() {
        function toFloat(value) {
            var number = parseFloat(value);
            return isNaN(number) ? 0 : number;
        }

        function calculateDimensionAverage($card) {
            var weightedSum = 0;
            var totalWeight = 0;

            $card.find('.criterion-row').each(function() {
                var $row = $(this);
                var score = toFloat($row.find('.score-input').val());
                var weight = toFloat($row.data('weight'));

                if (score > 0) {
                    weightedSum += (score * weight);
                    totalWeight += weight;
                }
            });

            var avg = totalWeight > 0 ? (weightedSum / totalWeight) : 0;
            $card.find('.dimension-average').text(avg.toFixed(2));

            return avg;
        }

        function calculateOverall() {
            var total = 0;
            var count = 0;
            var maxReference = 5;

            $('.dimension-card').each(function() {
                var avg = calculateDimensionAverage($(this));
                if (avg > 0) {
                    total += avg;
                    count++;
                }
            });

            var overall = count > 0 ? (total / count) : 0;
            var percentage = maxReference > 0 ? (overall / maxReference) * 100 : 0;

            $('#overall_preview').val(overall.toFixed(2));
            $('#overall_bar')
                .css('width', percentage.toFixed(0) + '%')
                .text(percentage.toFixed(0) + '%');

            if (percentage < 40) {
                $('#overall_bar').removeClass('bg-success bg-warning').addClass('bg-danger');
            } else if (percentage < 70) {
                $('#overall_bar').removeClass('bg-success bg-danger').addClass('bg-warning');
            } else {
                $('#overall_bar').removeClass('bg-warning bg-danger').addClass('bg-success');
            }
        }

        $(document).on('input', '.score-input', calculateOverall);

        calculateOverall();
    });
</script>
@endpush