<script setup lang="ts">
import { dashboard } from '@/routes'
import { Head, Link } from '@inertiajs/vue3'
</script>

<template>
    <Head title="Welcome">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]">

        <header class="mb-6 w-full max-w-[335px] text-sm lg:max-w-4xl">
            <nav class="flex items-center justify-end gap-4">

                <Link
                    v-if="$page.props.auth.user"
                    :href="dashboard().url"
                    class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC]"
                >
                    Dashboard
                </Link>

                <template v-else>
                    <Link
                        href="/login"
                        class="inline-block rounded-sm px-5 py-1.5 text-sm text-[#1b1b18] hover:border dark:text-[#EDEDEC]"
                    >
                        Log in
                    </Link>

                    <Link
                        href="/register"
                        class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC]"
                    >
                        Register
                    </Link>
                </template>

            </nav>
        </header>


        <div class="flex w-full items-center justify-center lg:grow">

            <main class="flex w-full max-w-[335px] flex-col-reverse overflow-hidden rounded-lg lg:max-w-4xl lg:flex-row">

                <div
                    class="flex-1 bg-white p-6 pb-12 text-[13px] leading-[20px] lg:p-20 dark:bg-[#161615] dark:text-[#EDEDEC]"
                >

                    <h1 class="mb-1 font-medium">Let's get started</h1>

                    <p class="mb-4 text-[#706f6c] dark:text-[#A1A09A]">
                        Laravel has an incredibly rich ecosystem. <br />
                        We suggest starting with the following.
                    </p>

                    <ul class="mb-6 flex flex-col gap-3">

                        <li class="flex items-center gap-3">

                            <span>Read the</span>

                            <a
                                href="https://laravel.com/docs"
                                target="_blank"
                                class="inline-flex items-center space-x-1 font-medium text-[#f53003] underline underline-offset-4"
                            >
                                <span>Documentation</span>

                                <svg
                                    width="10"
                                    height="11"
                                    viewBox="0 0 10 11"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-2.5 w-2.5"
                                >
                                    <path
                                        d="M7.70833 6.95834V2.79167H3.54167M2.5 8L7.5 3.00001"
                                        stroke="currentColor"
                                        stroke-linecap="square"
                                    />
                                </svg>

                            </a>

                        </li>


                        <li class="flex items-center gap-3">

                            <span>Watch tutorials at</span>

                            <a
                                href="https://laracasts.com"
                                target="_blank"
                                class="inline-flex items-center space-x-1 font-medium text-[#f53003] underline underline-offset-4"
                            >
                                <span>Laracasts</span>

                                <svg
                                    width="10"
                                    height="11"
                                    viewBox="0 0 10 11"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-2.5 w-2.5"
                                >
                                    <path
                                        d="M7.70833 6.95834V2.79167H3.54167M2.5 8L7.5 3.00001"
                                        stroke="currentColor"
                                        stroke-linecap="square"
                                    />
                                </svg>

                            </a>

                        </li>

                    </ul>


                    <a
                        href="https://cloud.laravel.com"
                        target="_blank"
                        class="inline-block rounded-sm border border-black bg-black px-5 py-1.5 text-sm text-white hover:bg-gray-800"
                    >
                        Deploy now
                    </a>

                </div>


                <div
                    class="flex items-center justify-center bg-[#fff2f2] p-10 lg:w-[438px] dark:bg-[#1D0002]"
                >

                    <svg
                        class="w-48 text-[#F53003]"
                        viewBox="0 0 438 104"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path d="M17.2036 -3H0V102.197H49.5189V86.7187H17.2036V-3Z" fill="currentColor"/>
                        <path d="M438 -3H421.694V102.197H438V-3Z" fill="currentColor"/>
                    </svg>

                </div>

            </main>

        </div>

    </div>
</template>