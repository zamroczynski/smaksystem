<script setup lang="ts">
// import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
// import { BookOpen, Folder } from 'lucide-vue-next';
import { LayoutGrid, User, Award, FileSliders, AlarmClockPlus, CalendarClock } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { computed } from 'vue';

const page = usePage();
const userPermissions = computed(() => page.props.auth.role_permissions || []);

interface NavItemWithPermission extends NavItem {
    permission?: string;
}

const allNavItems: NavItemWithPermission[] = [
    {
        title: 'Pulpit nawigacyjny',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Pracownicy',
        href: '/users',
        icon: User,
        permission: 'Edycja pracowników',
    },
    {
        title: 'Role',
        href: '/roles',
        icon: Award,
        permission: 'Edycja ról',
    },
    {
        title: 'Moje Preferencje',
        href: '/preferences',
        icon: FileSliders,
        permission: 'Moje Preferencje',
    },
    {
        title: 'Harmonogramy Zmian',
        href: '/shift-templates',
        icon: AlarmClockPlus,
        permission: 'Harmonogram Zmian',
    },
    {
        title: 'Tworzenie Grafików pracy',
        href: '/schedules',
        icon: CalendarClock,
        permission: 'Edycja Grafików Pracy',
    },
];

const filteredNavItems = computed(() => {
    return allNavItems.filter(item => {
        // Jeśli element nie ma określonego uprawnienia, jest zawsze widoczny (np. Pulpit nawigacyjny)
        if (!item.permission) {
            return true;
        }
        // Sprawdź, czy użytkownik posiada wymagane uprawnienie
        return userPermissions.value.includes(item.permission);
    });
});

// const footerNavItems: NavItem[] = [
//     {
//         title: 'Github Repo',
//         href: 'https://github.com/laravel/vue-starter-kit',
//         icon: Folder,
//     },
//     {
//         title: 'Documentation',
//         href: 'https://laravel.com/docs/starter-kits#vue',
//         icon: BookOpen,
//     },
// ];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="filteredNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <!-- <NavFooter :items="footerNavItems" /> -->
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
