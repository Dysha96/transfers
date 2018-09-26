<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8" name="csrf-token" content="{{ csrf_token() }}">

    <title>TRANSFERS</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet" type="text/css">
    <link href="{{asset('css/app.css')}}" rel="stylesheet" type="text/css">

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"  crossorigin="anonymous"></script>


    <!-- Styles -->
    <style>
        html, body {
            background-color: #fff;
            color: #636b6f;
            font-family: 'Nunito', sans-serif;
            font-weight: 200;
            height: 100vh;
            margin: 0;
        }

        .full-height {
            height: 100vh;
        }

        .flex-center {
            align-items: center;
            display: flex;
            justify-content: center;
        }

        .position-ref {
            position: relative;
        }

        .content {
            text-align: center;
        }

        .title {
            font-size: 84px;
        }

        .links > a {
            color: #636b6f;
            padding: 0 25px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: .1rem;
            text-decoration: none;
            text-transform: uppercase;
        }

        .m-b-md {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>

@if (count($errors) > 0)
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@foreach(['success','danger'] as $status)
    @if(session()->has($status))
        <div class="alert alert-{{$status}}">
            {{session()->get($status)}}
        </div>
    @endif
@endforeach

<div class="flex-center position-ref full-height">
    <div class="content">
        <div class="title m-b-md">
            TRANSFERS
        </div>
        {!! Form::open() !!}
        {{ csrf_field() }}
            <div class="input-group">

                <div class="form-group">
                    <label for="exampleFormControlSelect1">От кого перевод</label>
                    <select name="senderUser" class="form-control" id="exampleFormControlSelect1">
                        @foreach($users as $user)
                            <option
                                @if(old('senderUser') == $user->id)
                                selected="selected"
                                @endif
                                value={{$user->id}}
                            >
                                {{$user->first_name}} {{$user->last_name}}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="exampleFormControlSelect1">Кому перевод</label>
                    <select name="recipientUser" class="form-control" id="exampleFormControlSelect1">
                        @foreach($users as $user)
                            <option
                                @if(old('recipientUser') == $user->id)
                                selected="selected"
                                @endif
                                value={{$user->id}}
                            >
                                {{$user->first_name}} {{$user->last_name}}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="exampleFormControlSelect1">Сколько</label>
                    <div class="input-group mb-3">
                        <input name="amount" type="number" step=0.01 min=0.01 value="{{ old('amount') }}" class="form-control" >
                        <div class="input-group-append">
                            <span class="input-group-text">$</span>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="exampleFormControlSelect1">Когда</label>
                    <div class="input-group mb-3">
                        <input
                                name="date"
                                id="datetime-local"
                                type="datetime-local"
                                value=@if(old('date'))"{{ old('date')}}" @else {{$date}} @endif
                                step=3600 min={{$date}}
                        >
                        <div class="input-group-append">
                            <span class="input-group-text">⏰</span>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary mb-2">Перевести</button>

        {!! Form::close() !!}

        <div class="links">
            <a href="/transactions">Последние переводы</a>
        </div>
    </div>
</div>
</body>
</html>
