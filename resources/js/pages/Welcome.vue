<script setup lang="ts">
import { onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import TheNavbar from '@/components/Landing/TheNavbar.vue';
import HeroSection from '@/components/Landing/HeroSection.vue';
import MapPickupSection from '@/components/Landing/MapPickupSection.vue';
import HowItWorksSection from '@/components/Landing/HowItWorksSection.vue';
import WasteCategorySection from '@/components/Landing/WasteCategorySection.vue';
import PointsAchievementSection from '@/components/Landing/PointsAchievementSection.vue';
import SubscriptionSection from '@/components/Landing/SubscriptionSection.vue';
import UpcycleStoreSection from '@/components/Landing/UpcycleStoreSection.vue';
import ArticleSection from '@/components/Landing/ArticleSection.vue';
import TestimonialSection from '@/components/Landing/TestimonialSection.vue';
import FaqSection from '@/components/Landing/FaqSection.vue';
import CtaSection from '@/components/Landing/CtaSection.vue';
import TheFooter from '@/components/Landing/TheFooter.vue';

interface WasteCategory {
    id: number;
    name: string;
    description: string;
    points_per_kg: number;
}

interface Subscription {
    id: number;
    name: string;
    price: number;
    description: string;
    nama?: string;
    harga?: number;
}

interface Product {
    id: number;
    name: string;
    description: string | null;
    price_rupiah: number;
    price_points: number;
    photo: string | null;
    nama_produk?: string;
    deskripsi?: string;
    harga_rupiah?: number;
    harga_poin?: number;
    foto?: string | null;
}

interface ArticleCategory {
    id: number;
    name: string;
}

interface Article {
    id: number;
    title: string;
    excerpt?: string;
    content?: string;
    thumbnail?: string | null;
    photo?: string | null;
    slug: string;
    category: ArticleCategory | null;
}

defineProps<{
    wasteCategories: WasteCategory[];
    subscriptions: Subscription[];
    products: Product[];
    articles: Article[];
    basemapApiKey?: string;
}>();

onMounted(() => {
    if (typeof window !== 'undefined') {
        gsap.registerPlugin(ScrollTrigger);

        // Pastikan ScrollTrigger menghitung posisi akurat setelah layout dan gambar termuat
        setTimeout(() => {
            ScrollTrigger.refresh();
        }, 300);

        window.addEventListener('load', () => {
            ScrollTrigger.refresh();
        });
    }
});
</script>

<template>
    <Head title="ReValue - Tingkatkan Value dari Sampahmu" />

    <div class="min-h-screen bg-white font-sans antialiased">
        <TheNavbar />

        <main>
            <HeroSection />
            <MapPickupSection :basemap-api-key="basemapApiKey" />
            <HowItWorksSection />
            <WasteCategorySection :categories="wasteCategories" />
            <PointsAchievementSection />
            <SubscriptionSection :subscriptions="subscriptions" />
            <UpcycleStoreSection :products="products" />
            <ArticleSection :articles="articles" />
            <TestimonialSection />
            <FaqSection />
            <CtaSection />
        </main>
        <TheFooter />
    </div>
</template>
