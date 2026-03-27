<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle text-muted text-muted waves-effect waves-dark" href="" data-toggle="dropdown"
       aria-haspopup="true"
       aria-expanded="false"> <i class="fas fa-bell"></i>
        <div class="{{ ($notification_prs == 0 && $notification_urs == 0) ? '': 'notify' }}">
            <span class="heartbit"></span>
            <span class="point"></span>
        </div>
    </a>
    <div class="dropdown-menu dropdown-menu-right mailbox scale-up">
        <ul>
            @php
            $total = ($notification_prs + $notification_urs);
            @endphp
            <li>
                <div class="drop-title">{{ $total }} {{$total >= 0 ? 'Notificaciones':'Notificacion'}}</div>
            </li>

            <li>

                <div class="message-center">
                    @if($notification_urs > 0)
                        <a href="{{route('uniform-request.index')}}">
                            <div class="btn btn-info btn-circle">
                                <i class="fa fa-link"></i>
                            </div>
                            <div class="mail-contnet">
                                <h5>Solicitud de uniformes</h5>
                            </span>
                                <span class="time">{{$notification_urs}}</span>
                            </div>
                        </a>
                    @endif
                    @if($notification_prs > 0)
                        <a href="{{route('payment-request.index')}}">
                            <div class="btn btn-info btn-circle">
                                <i class="fa fa-link"></i>
                            </div>
                            <div class="mail-contnet">
                                <h5>Comprobantes de pago</h5>
                            </span>
                                <span class="time">{{$notification_prs}}</span>
                            </div>
                        </a>
                    @endif
                    @if($total == 0)
                        <a href="javascript:void(0)">
                            <div class="btn btn-info btn-circle">
                                <i class="fa fa-link"></i>
                            </div>
                            <div class="mail-contnet">
                                <h5>No hay notificaciones</h5>
                            </div>
                        </a>
                    @endif

                </div>

            </li>
        </ul>
    </div>
</li>
