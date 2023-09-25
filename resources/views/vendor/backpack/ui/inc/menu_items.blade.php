{{-- This file is used for menu items by any Backpack v6 theme --}}
<x-backpack::menu-item title="Dashboard" icon="la la-home" :link="backpack_url('dashboard')" />
<x-backpack::menu-item title="Produtos" icon="la la-boxes" :link="backpack_url('produto')" />
<x-backpack::menu-item title="Estoque" icon="la la-dolly" :link="backpack_url('estoque')" />
<x-backpack::menu-item title="Unidade de Fornecimento" icon="la la-balance-scale-left" :link="backpack_url('uf')" />
<x-backpack::menu-item title="Registro de Entradas" icon="la la-cart-plus" :link="backpack_url('entrada')" />
<x-backpack::menu-item title="Registro de Saidas" icon="la la-cart-arrow-down" :link="backpack_url('saida')" />
<x-backpack::menu-item title="Activitylogs" icon="la la-question" :link="backpack_url('activitylog')" />