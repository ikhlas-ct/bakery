    <div class="container-fluid d-flex justify-content-between">
        <nav class="pull-left">
            <ul class="nav">
                <li class="nav-item">
                    {{ date('Y') }}, made with <i class="fa fa-heart heart text-danger"></i>
                    by {{ $settings->nama ?? '-' }}
                </li>
            </ul>
        </nav>
    </div>
