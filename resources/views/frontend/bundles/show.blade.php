@extends('frontend.layouts.app'.config('theme_layout'))

@section('title', ($bundle->meta_title) ? $bundle->meta_title : app_name() )
@section('meta_description', $bundle->meta_description)
@section('meta_keywords', $bundle->meta_keywords)

@section('content')

    <link rel="stylesheet" href="/css/frontendplatinum.css">


    <!-- Start of banner
                                                                 ============================================= -->

    <div class="platinum">
        @if ($bundle->image != '')
            <div class="imagem-container">
                <div class="imagem">
                    <img src="{{ asset('storage/uploads/' . $bundle->image) }}" alt="">
                </div>
        @endif
        <div class="text-info-platinum">
            <h1>LOGO</h1>
            <h2>{{ $bundle->title }}</h2>
            {!! $bundle->description !!}
        </div>
    </div>
    <a href="#teste" id="scroll-button" role="button">
        <div class="form-button-platinum3">
            <span>QUERO ME MATRICULAR</span>
        </div>
    </a>
    <!-- End of banner
                                                                ============================================= -->

    <!-- Start of about-page section
                                                                ============================================= -->
    <section id="about-page" class="about-page-section-platinum">
        <div class="container-platinum">
            <div class="row">
                <div class="@if ($bundle->sidebar == 1) col-md-9 @else col-md-12 @endif ">
                    <div class="about-us-content-item">

                        <!-- /gallery -->

                        <div class="about-text-item-platinum">
                            <div class="section-title-2-platinum  headline text-left">
                                <h2>{{ $bundle->title }}</h2>
                            </div>
                            {!! $bundle->content !!}
                        </div>
                        <!-- /about-text -->
                    </div>
                </div>
                @if ($bundle->sidebar == 1)
                    @include('frontend.layouts.partials.right-sidebar')
                @endif
            </div>
        </div>
    </section>
    <!-- End of about-page section
                                                                        ============================================= -->
    <!-- Start of container-card
                                                                        ============================================= -->
    <section>
        <div class="container-card-platinum stars-platinum">
            <div class="card-platinum">
                <div class="img">
                    <span>{{ $bundle->titulotendencia1 }}</span>
                </div>
                <div class="content">

                    <p class="desc-card-platinum">{{ $bundle->tendencia1 }}</p>
                </div>
                <div class="arrow">
                    <span>&#8673;</span>
                </div>
            </div>
            <div class="card-platinum">
                <div class="img">
                    <span>{{ $bundle->titulotendencia2 }}</span>
                </div>
                <div class="content">

                    <p class="desc-card-platinum">{{ $bundle->tendencia2 }}</p>
                </div>
                <div class="arrow">
                    <span>&#8673;</span>
                </div>
            </div>
            <div class="card-platinum">
                <div class="img">
                    <span>{{ $bundle->titulotendencia3 }}</span>
                </div>
                <div class="content">

                    <p class="desc-card-platinum">{{ $bundle->tendencia3 }}</p>
                </div>
                <div class="arrow">
                    <span>&#8673;</span>
                </div>
            </div>
        </div>
    </section>
    <!-- End of container-card
                                                                    ============================================= -->
    <!-- Start of element-section
                                                                    ============================================= -->
    <section class="element-section-platinum">
        <div class="element-platinum">
            <div class="title-element-platinum">
                <p class="p-element-platinum">O que temos de especial que ninguém mais tem</p>
            </div>
            <div class="cards-container">
                <div class="element-card-platinum">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <div class="card-content-platinum">
                        <h2>{{ $bundle->titulocard1 }}</h2>
                        <p>{{ $bundle->card1 }}</p>
                    </div>
                </div>
                <div class="element-card-platinum">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <div class="card-content-platinum">
                        <h2>{{ $bundle->titulocard2 }}</h2>
                        <p>{{ $bundle->card2 }}</p>
                    </div>
                </div>
                <div class="element-card-platinum">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <div class="card-content-platinum">
                        <h2>{{ $bundle->titulocard3 }}</h2>
                        <p>{{ $bundle->card3 }}</p>
                    </div>
                </div>
            </div>
    </section>
    <!-- End of element-section
                                                                   ============================================= -->
    <!-- Start of subscribe
                                                                   ============================================= -->
    <section class="container-form-platinum">
        <section>
            <div id="teste" class="content-form-platinum">
                <div class="form-title-platinum">
                    <h2> Matricule-se agora </h2>
                </div>
                <div class="form-x-platinum">
                    <h2> 12x </h2>
                </div>
                <div class="form-price-platinum">
                    <h2> {{ round($bundle->price / 12) }} </h2>
                </div>
                <div class="form-2-platinum">
                    <h2> ,{{ str_pad(round(($bundle->price / 12 - floor($bundle->price / 12)) * 100), 2, '0', STR_PAD_LEFT) }}
                    </h2>
                </div>
                <div class="form-info-platinum">
                    <h2> ou {{ $appCurrency['symbol'] . ' ' . $bundle->price }} à vista </h2>
                </div>

                <div class="container-form-button">
                    <form action="{{ route('cart.checkout') }}" method="POST">
                        @csrf
                        <input type="hidden" name="bundle_id" value="{{ $bundle->id }}" />
                        <input type="hidden" name="amount" value="{{ $bundle->price }}" />
                        <button class="form-button-platinum" href="#"> <span><b>QUERO PAGAR COM CARTÃO
                                    <br>
                                    DE CRÉDITO</b> - EM ATÉ 12X</span> </button>
                    </form>
                    <form action="{{ route('cart.checkout') }}" method="POST">
                        @csrf
                        <input type="hidden" name="bundle_id" value="{{ $bundle->id }}" />
                        <input type="hidden" name="amount" value="{{ $bundle->price }}" />
                        <button class="form-button-platinum2" href="#"> <span><b> QUERO PAGAR COM BOLETO
                                    <br>
                                    OU PIX </b> - À VISTA</span></button>
                    </form>
                </div>
            </div>
            <script>
                document.getElementById('scroll-button').addEventListener('click', function(event) {
                    event.preventDefault();
                    var target = document.getElementById(this.getAttribute('href').substring(1));
                    var scrollToPosition = target.offsetTop;
                    window.scrollTo({
                        top: scrollToPosition,
                        behavior: 'smooth'
                    });
                });
            </script>
        </section>
    </section>
    <!-- End of subscribe
                                                                   ============================================= -->
    <!-- Start of course
                                                                   ============================================= -->
    <section class="ofcourse-platinum">
        <div class="titlecourse-platinum">
            <h2> O que você vai aprender?</h2>
        </div>
        <div class="modules-platinum">
            <ul class="submodules">
                @foreach($lessons as $lesson)
                    <li>
                        <button class="module-link">{{ $lesson->title }}</button>
                    </li>
                @endforeach
                {{-- <li>
                    <button onclick="toggleSubmodules(1)">Módulo 01 - Análise de dados </button>
                    <ul class="submodule-1">
                        <li>Aula &nbsp 1: Mineração de dados</li>
                        <li>Aula &nbsp 2: Análise preditiva</li>
                        <li>Aula &nbsp 3: Visualização de dados</li>
                        <li>Aula &nbsp 4: Análise de sentimento</li>
                    </ul>
                </li>
                <li>
                    <button onclick="toggleSubmodules(2)">Módulo 02 - Marketing Digital</button>
                    <ul class="submodule-2">
                        <li>Aula &nbsp 1: SEO</li>
                        <li>Aula &nbsp 2: Marketing de Conteúdo</li>
                        <li>Aula &nbsp 3: E-mail marketing</li>
                        <li>Aula &nbsp 4: Mídias Sociais</li>
                    </ul>
                </li>
                <li>
                    <button onclick="toggleSubmodules(3)">Módulo 03 - Inteligência Artificial </button>
                    <ul class="submodule-3">
                        <li>Aula &nbsp 1: Aprendizado de máquina</li>
                        <li>Aula &nbsp 2: Redes neurais</li>
                        <li>Aula &nbsp 3: Processamento de Linguagem Natural</li>
                        <li>Aula &nbsp 4: Visão computacional</li>
                    </ul>
                </li>
                <li>
                    <button onclick="toggleSubmodules(4)">Módulo 04 - Blockchain</button>
                    <ul class="submodule-4">
                        <li>Aula &nbsp 1: Criptomoedas</li>
                        <li>Aula &nbsp 2: Contratos inteligentes </li>
                        <li>Aula &nbsp 3: Identidade digital</li>
                        <li>Aula &nbsp 4: Supply Chain</li>
                    </ul>
                </li>
                <li>
                    <button onclick="toggleSubmodules(5)">Módulo 05 - Internet das coisas (IoT)</button>
                    <ul class="submodule-5">
                        <li>Aula &nbsp 1: Sensores</li>
                        <li>Aula &nbsp 2: Sistemas embarcados </li>
                        <li>Aula &nbsp 3: Análise de dados em tempo real</li>
                        <li>Aula &nbsp 4: Dispositivos vestíveis</li>
                    </ul>
                </li>
                <li>
                    <button onclick="toggleSubmodules(6)">Módulo 06 - Computação em nuvem</button>
                    <ul class="submodule-6">
                        <li>Aula &nbsp 1: Armazenamento em nuvem</li>
                        <li>Aula &nbsp 2: Serviços de infraestrutura em nuvem </li>
                        <li>Aula &nbsp 3: Plataformas como serviço (PaaS)</li>
                        <li>Aula &nbsp 4: Software como serviço (SaaS)</li>
                    </ul>
                </li>
                <li>
                    <button onclick="toggleSubmodules(7)">Módulo 07 - Automação de processos</button>
                    <ul class="submodule-7">
                        <li>Aula &nbsp 1: Automação de tarefas manuais</li>
                        <li>Aula &nbsp 2: Redução de erros humanos </li>
                        <li>Aula &nbsp 3: Processos de back-office</li>
                        <li>Aula &nbsp 4: Integração com inteligência artificial</li>
                    </ul>
                </li>
                <li>
                    <button onclick="toggleSubmodules(8)">Módulo 08 - Cibersegurança</button>
                    <ul class="submodule-8">
                        <li>Aula &nbsp 1: Prevenção de ameaças</li>
                        <li>Aula &nbsp 2: Detecção de intrusos </li>
                        <li>Aula &nbsp 3: Gerenciamento de riscos</li>
                        <li>Aula &nbsp 4: Conformidade com regulamentações</li>
                    </ul>
                </li>
                <li>
                    <button onclick="toggleSubmodules(9)">Módulo 09 - Transformação digital</button>
                    <ul class="submodule-9">
                        <li>Aula &nbsp 1: Cultura organizacional</li>
                        <li>Aula &nbsp 2: Estratégia de negócios </li>
                        <li>Aula &nbsp 3: Inovação de produtos e serviços</li>
                        <li>Aula &nbsp 4: Experiência do cliente.</li>
                    </ul>
                </li> --}}
            </ul>
            <script>
                function toggleSubmodules(moduleIndex) {
                    const submodules = document.querySelectorAll('.submodules ul');

                    submodules.forEach((submodule) => {
                        submodule.classList.remove('open');
                    });

                    const selectedSubmodule = document.querySelector(`.submodule-${moduleIndex}`);
                    selectedSubmodule.classList.toggle('open');
                }
            </script>

    </section>


    <!-- End of course
                                                                 ============================================= -->

    <!-- Start Contacts
                                                             ============================================= -->
    <section class="contact-platinum">
        <h2 class="nt-h2"> Tem alguma dúvida? Fale conosco</h2>
        <div class="formas-contatos">
            <h2 class="wpp"> Enviar <span>Whatsapp</span></h2>
            <h2 class="email"> Enviar <span>Email</span></h2>
        </div>
        <div class="contact-platinum-button">
            <div class="contact-button-platinum">
                <a href="https://web.whatsapp.com" role="button-platinum">
                    <span><b>Enviar Mensagem</b></span>
            </div>
            </a>
            <div class="contact-button-platinum2">
                <a href="https://mail.google.com/mail/u/0/#inbox" role="button">
                    <span><b>Enviar Mensagem</b></span>
            </div>
            </a>
        </div>
    </section>
    <div class="div_wave">
        <svg class="hero-waves" viewBox="0 24 150 28" preserveAspectRatio="none">
            <defs>
                <path id="wave-path-1"
                    d="M-160 44c30 0 
                   58-18 
                   88-18s 
                   58 18 
                   88 18 
                   58-18 
                   88-18 
                   58 18 
                   88 18 
                   v56h-352z" />
                <path id="wave-path-2"
                    d="M-160 44c30 0 
                   58 18 
                   88 18s 
                   58-18 
                   88-18 
                   58 18 
                   88 18 
                   58-18 
                   88-18 
                   v56h-352z" />
                <path id="wave-path-3"
                    d="M-160 44c30 0 
                   58-18 
                   88-18s 
                   58 18 
                   88 18 
                   58-18 
                   88-18 
                   58 18 
                   88 18 
                   v56h-352z" />
            </defs>
            <g class="wave1">
                <use xlink:href="#wave-path-1" x="50" y="3" fill="#46464629" />
            </g>
            <g class="wave2">
                <use xlink:href="#wave-path-2" x="50" y="0" fill="#1a1a1a29" />
            </g>
            <g class="wave3">
                <use xlink:href="#wave-path-3" x="50" y="9" fill="#171717" />
            </g>
        </svg>
    </div>

    <!-- End Contacts
                                                             ============================================= -->
    <!-- Start Footer
                                                                 ============================================= -->
    <footer class="platinum-footer">
        <div id="footer_content-platinum">
            <div id="footer_contacts">
                <h1>Logo</h1>
                <p>It's all about your dreams.</p>

                <div id="footer_social_media">
                    <a href="#" class="footer-link" id="instagram">
                        <i class="fab fa-instagram"></i>
                    </a>

                    <a href="#" class="footer-link" id="facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>

                    <a href="#" class="footer-link" id="whatsapp">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                </div>
            </div>

            <ul class="footer-list">
                <li>
                    <h3>Blog</h3>
                </li>
                <li>
                    <a href="#" class="footer-link">Tech</a>
                </li>
                <li>
                    <a href="#" class="footer-link">Adventures</a>
                </li>
                <li>
                    <a href="#" class="footer-link">Music</a>
                </li>
            </ul>

            <ul class="footer-list">
                <li>
                    <h3>Products</h3>
                </li>
                <li>
                    <a href="#" class="footer-link">App</a>
                </li>
                <li>
                    <a href="#" class="footer-link">Desktop</a>
                </li>
                <li>
                    <a href="#" class="footer-link">Cloud</a>
                </li>
            </ul>

            <div id="footer_subscribe">
                <h3>Subscribe</h3>

                <p>
                    Enter your e-mail to get notified about
                    our news solutions
                </p>

                <div id="input_group">
                    <input type="email" id="email">
                    <button>
                        <i class="fas fa-mail-bulk"></i>
                    </button>
                </div>
            </div>
        </div>
    </footer>
    <!-- End Footer
                                                                 ============================================= -->