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
        <h2>{{$tentradas}}</h2>
      </div>
    </div>
  </div>
  <div class="col">
    <div class="card border-light">
      <div class="card-body text-center bg-warning rounded">
        <h5>Total de Saidas no mês</h5>
        <h2>{{$tsaidas}}</h2>
      </div>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-6">
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
  <div class="col-6">
    <div class="card">
      <div class="card-body text-center">
        <h5>Gráfico</h5>
      </div>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-6">
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
  <div class="col-6">
    <div class="card">
      <div class="card-body text-center">
        <h5>Gráfico</h5>
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
