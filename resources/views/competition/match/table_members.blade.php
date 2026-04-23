<div>
    <div class="match-table-wrapper">
        <div class="table-responsive">
            <table class="table table-bordered table-sm match-table" width="100%">
                <thead>
                <tr>
                    <th class="match-player-cell">Deportista</th>
                    <th class="match-metric-cell">Asist.</th>
                    <th class="match-metric-cell">Tit.</th>
                    <th class="match-metric-cell">Min.</th>
                    <th class="match-position-cell">Pos.</th>
                    <th class="match-metric-cell">Goles</th>
                    <th class="match-metric-cell">Ast. gol</th>
                    <th class="match-metric-cell">Ataj.</th>
                    <th class="match-metric-cell">TA</th>
                    <th class="match-metric-cell">TR</th>
                    <th class="match-metric-cell">Calif.</th>
                    <!-- <th class="match-observation-cell">Obs.</th> -->
                </tr>
                </thead>
                <tbody id="body_members">
                {!! $information->rows !!}
                </tbody>
            </table>
        </div>
    </div>
</div>
