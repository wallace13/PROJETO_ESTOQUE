<div class="row text-white">
  <div class="col">
    <div class="card border-light">
      <div class="card-body text-center bg-primary rounded">
        <h5>Total de Itens no Estoque</h5>
        <h2>{{$estoque}}</h2>
      </div>
    </div>
  </div>
  <div class="col ml-3 mr-3">
    <div class="card border-light">
      <div class="card-body text-center bg-success rounded">
        <h5>Total de Entrada no mês</h5>
        <h2 id='entradas'>{{$tentradas}}</h2>
      </div>
    </div>
  </div>
  <div class="col">
    <div class="card border-light">
      <div class="card-body text-center bg-warning rounded">
        <h5>Total de Saidas no mês</h5>
        <h2 id='saidas'>{{$tsaidas}}</h2>
      </div>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-4">
    <div class="card">
      <div class="card-body text-center">
        <h5>Últimas Entradas</h5>
        <table class="table">
          <thead>
            <tr>
              <th scope="col">Produto</th>
              <th scope="col">Uf</th>
              <th scope="col">Quantidade</th>
            </tr>
          </thead>
          <tbody>
            @foreach($entradas as $entrada)
            <tr>
              <td>{{ $entrada->estoque->produto->nome }}</td>
              <td>{{ $entrada->estoque->produto->ufs->uf }}</td>
              <td>{{ $entrada->quantidade }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-4">
    <div class="card">
      <div class="card-body text-center">
        <h5>Validades à vencer</h5>
        <table class="table">
          <thead>
            <tr>
              <th scope="col">Produto</th>
              <th scope="col">Validade</th>
              <th scope="col">Quantidade</th>
            </tr>
          </thead>
          <tbody>
            @foreach($validades as $validade)
            <tr @if(Carbon\Carbon::now()->gt($validade->validade)) class="text-danger" @endif>
                <td>{{ $validade->estoque->produto->nome }} - {{ $validade->estoque->produto->ufs->uf }}</td>
                <td>{{ \Carbon\Carbon::parse($validade->validade)->format('d/m/Y') }}</td>
                <td>{{ $validade->quantidade }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-4">
    <div class="card">
      <div class="card-body text-center">
        <h5>Últimas Saidas</h5>
        <table class="table">
          <thead>
            <tr>
              <th scope="col">Produto</th>
              <th scope="col">Uf</th>
              <th scope="col">Quantidade</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              @foreach($saidas as $saida)
              <tr>
                <td>{{ $saida->estoque->produto->nome }}</td>
                <td>{{ $saida->estoque->produto->ufs->uf }}</td>
                <td>{{ $saida->quantidade }}</td>
              </tr>
              @endforeach
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-6">
    <div class="card">
      <div class="card-body text-center">
        <h5>Gráfico de Entradas e Saidas</h5>
          <div id="graficoContainer" style="height: 200px; width: 100%;">
              <canvas id="pizzaEntradaSaida"></canvas>
          </div>
      </div>
    </div>
  </div>
  <div class="col-6">
    <div class="card">
      <div class="card-body text-center">
        <h5>Gráfico de Entradas e Saidas</h5>
          <div id="graficoContainer" style="height: 200px; width: 100%;">
              <canvas id="colunaEntradaSaida"></canvas>
          </div>
      </div>
    </div>
  </div>
</div>
@push('after_styles')
  @basset('https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.6.0/styles/base16/dracula.min.css')
@endpush

@push('after_scripts')
  @basset('https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.6.0/highlight.min.js')
  <script>hljs.highlightAll();</script>
@endpush

<script src="{{ asset('js/dashboard.js') }}"></script>