# FlatNest Web App — Full Implementation Guide

> This document is a complete, self-contained implementation guide for an AI agent to build the FlatNest web application from scratch. Read it fully before writing any code.

---

## Context

FlatNest is a flat/apartment rental platform in Bangladesh. There is already:
- A **Laravel REST API** at `https://flatnest.techrealify.com/api/v1`
- A **Laravel admin panel** (Blade) on the same server
- A **mobile app** consuming the same API

We are building a **web frontend** (Next.js) for **renters and owners** that consumes the existing API. No backend changes are needed for the web app itself — only the auth gate changes listed in Part 1.

---

## Part 1 — Backend Changes (Laravel)

These must be done before building the frontend.

### 1.1 Route Changes (`routes/api.php`)

Move `GET /listings/{id}` and `GET /listings/nearby` inside the `auth:sanctum` middleware group. Keep `GET /listings` public.

**Current (lines 25–28):**
```php
// Listings — public
Route::get('/listings',         [ListingController::class, 'index']);
Route::get('/listings/nearby',  [ListingController::class, 'nearby']);
Route::get('/listings/{id}',    [ListingController::class, 'show']);
```

**Change to:**
```php
// Listings — public browse (no owner contact in response)
Route::get('/listings', [ListingController::class, 'index']);

// Inside auth:sanctum group, add:
Route::get('/listings/nearby', [ListingController::class, 'nearby']);
Route::get('/listings/{id}',   [ListingController::class, 'show']);
```

> IMPORTANT: `listings/nearby` must be registered BEFORE `listings/{id}` inside the auth group to avoid route conflict.

### 1.2 ListingController — Gate Filters for Guests (`app/Http/Controllers/Api/V1/ListingController.php`)

In the `index()` method, add this at the top:

```php
public function index(Request $request): JsonResponse
{
    $filterParams = ['listing_type_id','price_min','price_max','beds','baths',
                     'facing_id','floor_min','floor_max','size_min','size_max',
                     'available_from_start','available_from_end','amenities',
                     'division_id','district_id','upazila_id','union_id','sort_by','search'];

    if ($request->hasAny($filterParams) && !auth('sanctum')->check()) {
        return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
    }

    // ... rest of existing index logic
}
```

> Add `auth('sanctum')->user()` at the top of the method to optionally resolve the auth user without blocking guests.

### 1.3 ListingResource — Hide Owner Contact for Guests (`app/Http/Resources/ListingResource.php`)

Replace the owner contact fields block (lines 63–67) with:

```php
'owner_name'        => $this->when(auth('sanctum')->check(), $this->owner_name),
'owner_phone'       => $this->when(auth('sanctum')->check(), $this->owner_phone),
'owner_alt_phone'   => $this->when(auth('sanctum')->check(), $this->owner_alt_phone),
'owner_email'       => $this->when(auth('sanctum')->check(), $this->owner_email),
'preferred_contact' => $this->when(auth('sanctum')->check(), $this->preferred_contact),
```

Also update the `owner` relation line (line 72):
```php
'owner' => $this->when(auth('sanctum')->check(), new UserResource($this->whenLoaded('owner'))),
```

### 1.4 CORS — Allow Next.js Frontend (`config/cors.php`)

Add your Next.js domain to `allowed_origins`:
```php
'allowed_origins' => [
    'http://localhost:3000',
    'https://your-app.vercel.app',
    'https://app.flatnest.com', // if you have a custom domain
],
```

---

## Part 2 — Frontend Setup (Next.js)

### 2.1 Create Project

```bash
npx create-next-app@latest flatnest-web --typescript --tailwind --eslint --app --src-dir
cd flatnest-web
```

### 2.2 Install Dependencies

```bash
npm install \
  axios \
  zustand \
  @tanstack/react-query \
  @tanstack/react-query-devtools \
  react-hook-form \
  zod \
  @hookform/resolvers \
  leaflet \
  react-leaflet \
  @types/leaflet \
  lucide-react \
  clsx \
  tailwind-merge \
  date-fns

# shadcn/ui
npx shadcn@latest init
# When prompted: Default style, Neutral color, yes to CSS variables
# Then add components as needed:
npx shadcn@latest add button card input label sheet dialog badge skeleton tabs avatar dropdown-menu toast separator
```

### 2.3 Project Structure

```
src/
├── app/
│   ├── (auth)/
│   │   ├── login/page.tsx
│   │   └── register/
│   │       ├── page.tsx          # Step 1 — account
│   │       ├── role/page.tsx     # Step 2 — role
│   │       └── avatar/page.tsx   # Step 3 — photo
│   ├── (renter)/
│   │   ├── page.tsx              # Home / Discovery (public)
│   │   ├── listings/
│   │   │   └── [id]/page.tsx     # Listing detail (auth required)
│   │   ├── map/page.tsx          # Map view (auth required)
│   │   ├── wishlist/page.tsx     # Saved listings (auth required)
│   │   └── messages/
│   │       ├── page.tsx          # Chat list
│   │       └── [id]/page.tsx     # Chat detail
│   ├── (owner)/
│   │   ├── dashboard/page.tsx    # Owner listings dashboard
│   │   ├── listings/
│   │   │   ├── create/page.tsx   # Create listing wizard
│   │   │   └── [id]/edit/page.tsx
│   │   └── messages/page.tsx
│   ├── notifications/page.tsx
│   ├── profile/page.tsx
│   └── layout.tsx
├── components/
│   ├── listing/
│   │   ├── ListingCard.tsx
│   │   ├── ListingGrid.tsx
│   │   ├── FilterSheet.tsx
│   │   └── ListingTypeTabs.tsx
│   ├── auth/
│   │   ├── AuthGuard.tsx
│   │   └── AuthModal.tsx         # "Login to continue" gate modal
│   ├── layout/
│   │   ├── Navbar.tsx
│   │   ├── BottomNav.tsx
│   │   └── PageWrapper.tsx
│   ├── map/
│   │   └── NearbyMap.tsx
│   └── chat/
│       ├── ChatList.tsx
│       └── MessageThread.tsx
├── lib/
│   ├── axios.ts                  # Axios instance + interceptors
│   ├── utils.ts                  # cn(), image URL resolver
│   └── constants.ts
├── hooks/
│   ├── useAuth.ts
│   ├── useListings.ts
│   ├── useWishlist.ts
│   ├── useChats.ts
│   └── useNotifications.ts
├── store/
│   └── auth.store.ts             # Zustand auth store
└── types/
    └── api.ts                    # All TypeScript types
```

---

## Part 3 — TypeScript Types (`src/types/api.ts`)

```typescript
export interface User {
  id: string
  name: string
  email: string
  phone: string | null
  role: 'renter' | 'owner' | null
  avatar_url: string | null
  is_complete: boolean
}

export interface ListingType {
  id: number
  name: string
  label: string
  slug: string
}

export interface Amenity {
  id: number
  name: string
  label: string
}

export interface ListingFacing {
  id: number
  label: string
  slug: string
}

export interface ListingPhoto {
  id: string
  url: string
  position: number
}

export interface ListingOwner {
  id: string
  name: string
  phone: string | null
  avatar_url: string | null
}

export interface Listing {
  id: string
  title: string
  area: string | null
  road_and_house: string | null
  price: number
  deposit: number | null
  beds: number | null
  baths: number | null
  size: number | null
  floor_no: number | null
  facing_id: number | null
  facing: ListingFacing | null
  available_from: string | null
  description: string | null
  status: 'draft' | 'submitted' | 'approved' | 'rejected' | 'rented'
  status_label: string
  views: number
  coord_x: number | null
  coord_y: number | null
  distance_km: number | null
  listing_type_id: number | null
  type: string
  division_id: number | null
  district_id: number | null
  upazila_id: number | null
  union_id: number | null
  amenities: Amenity[]
  photos: ListingPhoto[]
  owner: ListingOwner | null
  owner_name: string | null
  owner_phone: string | null
  owner_alt_phone: string | null
  owner_email: string | null
  preferred_contact: 'phone' | 'email' | 'chat' | null
  created_at: string
}

export interface OwnerListing extends Listing {
  inquiries: number
  rejection_reason: string | null
}

export interface ChatMessage {
  id: string
  chat_id: string
  sender_id: string
  sender: { id: string; name: string; avatar_url: string | null } | null
  text: string
  is_read: boolean
  created_at: string
}

export interface Chat {
  id: string
  listing: { id: string; title: string; area: string | null }
  other_user: { id: string; name: string; avatar_url: string | null }
  last_message: ChatMessage | null
  unread_count: number
  updated_at: string
}

export interface Notification {
  id: string
  kind: string
  title: string
  body: string
  time: string
  is_unread: boolean
  reference_id: string | null
}

export interface PaginatedResponse<T> {
  success: boolean
  data: T[]
  meta: {
    current_page: number
    last_page: number
    per_page: number
    total: number
    unread_count?: number
  }
}

export interface ApiResponse<T> {
  success: boolean
  message?: string
  data: T
}

export interface ListingFilters {
  search?: string
  listing_type_id?: number
  price_min?: number
  price_max?: number
  beds?: number
  baths?: number
  facing_id?: number
  floor_min?: number
  floor_max?: number
  size_min?: number
  size_max?: number
  available_from_start?: string
  available_from_end?: string
  amenities?: string
  division_id?: number
  district_id?: number
  upazila_id?: number
  union_id?: number
  sort_by?: 'price_asc' | 'price_desc' | 'newest' | 'oldest'
}
```

---

## Part 4 — Auth Store (`src/store/auth.store.ts`)

```typescript
import { create } from 'zustand'
import { persist } from 'zustand/middleware'
import { User } from '@/types/api'

interface AuthState {
  accessToken: string | null
  refreshToken: string | null
  user: User | null
  isAuthenticated: boolean
  setTokens: (access: string, refresh: string) => void
  setUser: (user: User) => void
  logout: () => void
}

export const useAuthStore = create<AuthState>()(
  persist(
    (set) => ({
      accessToken: null,
      refreshToken: null,
      user: null,
      isAuthenticated: false,
      setTokens: (access, refresh) =>
        set({ accessToken: access, refreshToken: refresh, isAuthenticated: true }),
      setUser: (user) => set({ user }),
      logout: () =>
        set({ accessToken: null, refreshToken: null, user: null, isAuthenticated: false }),
    }),
    { name: 'flatnest-auth' }
  )
)
```

---

## Part 5 — Axios Instance (`src/lib/axios.ts`)

```typescript
import axios from 'axios'
import { useAuthStore } from '@/store/auth.store'

const BASE_URL = 'https://flatnest.techrealify.com/api/v1'

const api = axios.create({
  baseURL: BASE_URL,
  headers: { Accept: 'application/json' },
})

// Attach token to every request
api.interceptors.request.use((config) => {
  const token = useAuthStore.getState().accessToken
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

// Silent token refresh on 401
let isRefreshing = false
let failedQueue: any[] = []

const processQueue = (error: any, token: string | null = null) => {
  failedQueue.forEach((prom) => (error ? prom.reject(error) : prom.resolve(token)))
  failedQueue = []
}

api.interceptors.response.use(
  (res) => res,
  async (error) => {
    const original = error.config
    if (error.response?.status === 401 && !original._retry) {
      if (isRefreshing) {
        return new Promise((resolve, reject) => {
          failedQueue.push({ resolve, reject })
        }).then((token) => {
          original.headers.Authorization = `Bearer ${token}`
          return api(original)
        })
      }
      original._retry = true
      isRefreshing = true
      const refreshToken = useAuthStore.getState().refreshToken
      try {
        const { data } = await axios.post(`${BASE_URL}/auth/refresh`, { refresh_token: refreshToken })
        const newToken = data.data.access_token
        useAuthStore.getState().setTokens(newToken, refreshToken!)
        processQueue(null, newToken)
        original.headers.Authorization = `Bearer ${newToken}`
        return api(original)
      } catch (err) {
        processQueue(err, null)
        useAuthStore.getState().logout()
        window.location.href = '/login'
        return Promise.reject(err)
      } finally {
        isRefreshing = false
      }
    }
    return Promise.reject(error)
  }
)

export default api
```

---

## Part 6 — Utility Functions (`src/lib/utils.ts`)

```typescript
import { clsx, type ClassValue } from 'clsx'
import { twMerge } from 'tailwind-merge'

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}

const BASE_URL = 'https://flatnest.techrealify.com'

export function resolveImageUrl(url: string | null | undefined): string {
  if (!url) return '/placeholder-listing.jpg'
  return url.startsWith('/') ? `${BASE_URL}${url}` : url
}

export function getThumbnail(photos: { url: string; position: number }[]): string {
  if (!photos?.length) return '/placeholder-listing.jpg'
  const sorted = [...photos].sort((a, b) => a.position - b.position)
  return resolveImageUrl(sorted[0].url)
}

export function formatPrice(price: number): string {
  return `৳${price.toLocaleString('en-BD')}`
}
```

---

## Part 7 — API Hooks (`src/hooks/`)

### `useListings.ts`

```typescript
import { useQuery } from '@tanstack/react-query'
import api from '@/lib/axios'
import { Listing, ListingFilters, PaginatedResponse } from '@/types/api'

export function useListings(filters?: ListingFilters) {
  return useQuery({
    queryKey: ['listings', filters],
    queryFn: async () => {
      const { data } = await api.get<{ data: Listing[] }>('/listings', { params: filters })
      return data.data
    },
  })
}

export function useListing(id: string) {
  return useQuery({
    queryKey: ['listing', id],
    queryFn: async () => {
      const { data } = await api.get<{ data: Listing }>(`/listings/${id}`)
      return data.data
    },
    enabled: !!id,
  })
}

export function useNearbyListings(coords: { coord_x: number; coord_y: number; radius?: number } | null) {
  return useQuery({
    queryKey: ['listings-nearby', coords],
    queryFn: async () => {
      const { data } = await api.get<{ data: Listing[] }>('/listings/nearby', { params: coords! })
      return data.data
    },
    enabled: !!coords,
  })
}

export function useListingTypes() {
  return useQuery({
    queryKey: ['listing-types'],
    queryFn: async () => {
      const { data } = await api.get('/listing-types')
      return data.data
    },
    staleTime: Infinity,
  })
}

export function useAmenities() {
  return useQuery({
    queryKey: ['amenities'],
    queryFn: async () => {
      const { data } = await api.get('/amenities')
      return data.data
    },
    staleTime: Infinity,
  })
}
```

### `useWishlist.ts`

```typescript
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import api from '@/lib/axios'

export function useWishlist() {
  return useQuery({
    queryKey: ['wishlist'],
    queryFn: async () => {
      const { data } = await api.get('/wishlist')
      return data.data
    },
  })
}

export function useToggleWishlist() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: async (listingId: string) => {
      const { data } = await api.post(`/wishlist/${listingId}/toggle`)
      return data.data as { saved: boolean }
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['wishlist'] })
    },
  })
}
```

### `useAuth.ts`

```typescript
import { useMutation } from '@tanstack/react-query'
import api from '@/lib/axios'
import { useAuthStore } from '@/store/auth.store'
import { useRouter } from 'next/navigation'

export function useLogin() {
  const { setTokens, setUser } = useAuthStore()
  const router = useRouter()
  return useMutation({
    mutationFn: async (creds: { email: string; password: string }) => {
      const { data } = await api.post('/auth/login', creds)
      return data.data
    },
    onSuccess: (data) => {
      setTokens(data.access_token, data.refresh_token)
      setUser(data.user)
      router.push(data.user.role === 'owner' ? '/dashboard' : '/')
    },
  })
}

export function useLogout() {
  const { logout } = useAuthStore()
  const router = useRouter()
  return useMutation({
    mutationFn: async () => api.post('/auth/logout'),
    onSuccess: () => {
      logout()
      router.push('/login')
    },
  })
}
```

### `useNotifications.ts`

```typescript
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import api from '@/lib/axios'

export function useNotifications(page = 1) {
  return useQuery({
    queryKey: ['notifications', page],
    queryFn: async () => {
      const { data } = await api.get('/notifications', { params: { page } })
      return data
    },
  })
}

export function useUnreadCount() {
  return useQuery({
    queryKey: ['notifications-unread'],
    queryFn: async () => {
      const { data } = await api.get('/notifications/unread-count')
      return data.data.unread_count as number
    },
    refetchInterval: 30000, // poll every 30s
  })
}

export function useMarkAllRead() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: () => api.patch('/notifications/read-all'),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ['notifications'] })
      qc.invalidateQueries({ queryKey: ['notifications-unread'] })
    },
  })
}
```

---

## Part 8 — Key Components

### `AuthGuard.tsx` — Redirect unauthenticated users

```tsx
'use client'
import { useAuthStore } from '@/store/auth.store'
import { useRouter } from 'next/navigation'
import { useEffect } from 'react'

export default function AuthGuard({ children }: { children: React.ReactNode }) {
  const { isAuthenticated } = useAuthStore()
  const router = useRouter()
  useEffect(() => {
    if (!isAuthenticated) router.push('/login')
  }, [isAuthenticated])
  if (!isAuthenticated) return null
  return <>{children}</>
}
```

### `AuthModal.tsx` — "Login to continue" gate for guests

```tsx
'use client'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { useRouter } from 'next/navigation'

interface AuthModalProps {
  open: boolean
  onClose: () => void
  message?: string
}

export default function AuthModal({ open, onClose, message = 'Sign in to continue' }: AuthModalProps) {
  const router = useRouter()
  return (
    <Dialog open={open} onOpenChange={onClose}>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Sign in required</DialogTitle>
          <DialogDescription>{message}</DialogDescription>
        </DialogHeader>
        <div className="flex gap-3 mt-4">
          <Button className="flex-1" onClick={() => router.push('/login')}>Log In</Button>
          <Button variant="outline" className="flex-1" onClick={() => router.push('/register')}>Sign Up</Button>
        </div>
      </DialogContent>
    </Dialog>
  )
}
```

### `ListingCard.tsx`

```tsx
'use client'
import Image from 'next/image'
import { Listing } from '@/types/api'
import { getThumbnail, formatPrice } from '@/lib/utils'
import { Bed, Bath, Maximize2, Heart } from 'lucide-react'
import { Badge } from '@/components/ui/badge'
import { useState } from 'react'
import AuthModal from '@/components/auth/AuthModal'
import { useAuthStore } from '@/store/auth.store'
import { useRouter } from 'next/navigation'
import { useToggleWishlist } from '@/hooks/useWishlist'

interface Props {
  listing: Listing
  saved?: boolean
}

export default function ListingCard({ listing, saved = false }: Props) {
  const { isAuthenticated } = useAuthStore()
  const [showAuthModal, setShowAuthModal] = useState(false)
  const router = useRouter()
  const toggleWishlist = useToggleWishlist()

  const handleCardClick = () => {
    if (!isAuthenticated) {
      setShowAuthModal(true)
      return
    }
    router.push(`/listings/${listing.id}`)
  }

  const handleSave = (e: React.MouseEvent) => {
    e.stopPropagation()
    if (!isAuthenticated) {
      setShowAuthModal(true)
      return
    }
    toggleWishlist.mutate(listing.id)
  }

  return (
    <>
      <div onClick={handleCardClick} className="cursor-pointer rounded-xl overflow-hidden border bg-white shadow-sm hover:shadow-md transition-shadow">
        <div className="relative h-48">
          <Image src={getThumbnail(listing.photos)} alt={listing.title} fill className="object-cover" />
          <button onClick={handleSave} className="absolute top-2 right-2 bg-white rounded-full p-1.5 shadow">
            <Heart className={`w-4 h-4 ${saved ? 'fill-red-500 text-red-500' : 'text-gray-400'}`} />
          </button>
          <Badge className="absolute bottom-2 left-2 bg-white text-black">
            {listing.type}
          </Badge>
        </div>
        <div className="p-3">
          <p className="font-semibold text-sm line-clamp-1">{listing.title}</p>
          <p className="text-xs text-gray-500 mt-0.5">{listing.area}</p>
          <p className="text-primary font-bold mt-1">{formatPrice(listing.price)}<span className="text-xs font-normal text-gray-500">/mo</span></p>
          <div className="flex gap-3 mt-2 text-xs text-gray-600">
            {listing.beds && <span className="flex items-center gap-1"><Bed className="w-3 h-3" />{listing.beds} Beds</span>}
            {listing.baths && <span className="flex items-center gap-1"><Bath className="w-3 h-3" />{listing.baths} Baths</span>}
            {listing.size && <span className="flex items-center gap-1"><Maximize2 className="w-3 h-3" />{listing.size} sqft</span>}
          </div>
          {listing.available_from === null && (
            <Badge variant="outline" className="mt-2 text-xs text-green-600 border-green-300">Available Now</Badge>
          )}
        </div>
      </div>
      <AuthModal
        open={showAuthModal}
        onClose={() => setShowAuthModal(false)}
        message="Sign in to view listing details and contact the owner."
      />
    </>
  )
}
```

### `FilterSheet.tsx`

```tsx
'use client'
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { SlidersHorizontal } from 'lucide-react'
import { useState } from 'react'
import { useAuthStore } from '@/store/auth.store'
import AuthModal from '@/components/auth/AuthModal'
import { ListingFilters } from '@/types/api'

interface Props {
  filters: ListingFilters
  onApply: (filters: ListingFilters) => void
}

export default function FilterSheet({ filters, onApply }: Props) {
  const { isAuthenticated } = useAuthStore()
  const [showAuthModal, setShowAuthModal] = useState(false)
  const [open, setOpen] = useState(false)
  const [local, setLocal] = useState<ListingFilters>(filters)

  const handleOpen = () => {
    if (!isAuthenticated) {
      setShowAuthModal(true)
      return
    }
    setOpen(true)
  }

  return (
    <>
      <Button variant="outline" size="sm" onClick={handleOpen}>
        <SlidersHorizontal className="w-4 h-4 mr-1" /> Filter
      </Button>
      <Sheet open={open} onOpenChange={setOpen}>
        <SheetContent side="bottom" className="h-[80vh] overflow-y-auto">
          <SheetHeader><SheetTitle>Filter Listings</SheetTitle></SheetHeader>
          <div className="space-y-4 py-4">
            <div className="grid grid-cols-2 gap-3">
              <div>
                <Label>Min Price (৳)</Label>
                <Input type="number" placeholder="e.g. 10000"
                  value={local.price_min ?? ''}
                  onChange={e => setLocal(p => ({ ...p, price_min: Number(e.target.value) || undefined }))} />
              </div>
              <div>
                <Label>Max Price (৳)</Label>
                <Input type="number" placeholder="e.g. 30000"
                  value={local.price_max ?? ''}
                  onChange={e => setLocal(p => ({ ...p, price_max: Number(e.target.value) || undefined }))} />
              </div>
            </div>
            <div className="grid grid-cols-2 gap-3">
              <div>
                <Label>Bedrooms</Label>
                <Input type="number" placeholder="e.g. 2"
                  value={local.beds ?? ''}
                  onChange={e => setLocal(p => ({ ...p, beds: Number(e.target.value) || undefined }))} />
              </div>
              <div>
                <Label>Bathrooms</Label>
                <Input type="number" placeholder="e.g. 1"
                  value={local.baths ?? ''}
                  onChange={e => setLocal(p => ({ ...p, baths: Number(e.target.value) || undefined }))} />
              </div>
            </div>
            {/* Add more filter fields: amenities checkboxes, geo dropdowns, sort, etc. */}
            <div className="flex gap-3 pt-4">
              <Button variant="outline" className="flex-1" onClick={() => { setLocal({}); onApply({}) }}>Reset</Button>
              <Button className="flex-1" onClick={() => { onApply(local); setOpen(false) }}>Apply Filters</Button>
            </div>
          </div>
        </SheetContent>
      </Sheet>
      <AuthModal open={showAuthModal} onClose={() => setShowAuthModal(false)}
        message="Sign in to filter and search listings." />
    </>
  )
}
```

---

## Part 9 — Pages

### Home / Discovery Page (`src/app/(renter)/page.tsx`)

This is the public landing page. Shows listing cards. Auth modal on filter/detail click.

```tsx
'use client'
import { useState } from 'react'
import { useListings, useListingTypes } from '@/hooks/useListings'
import ListingCard from '@/components/listing/ListingCard'
import FilterSheet from '@/components/listing/FilterSheet'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { Skeleton } from '@/components/ui/skeleton'
import { ListingFilters } from '@/types/api'
import { Search } from 'lucide-react'
import { useDebounce } from 'use-debounce' // npm install use-debounce

export default function HomePage() {
  const [filters, setFilters] = useState<ListingFilters>({})
  const [search, setSearch] = useState('')
  const [debouncedSearch] = useDebounce(search, 500)
  const [selectedType, setSelectedType] = useState<number | undefined>()

  const activeFilters = { ...filters, search: debouncedSearch || undefined, listing_type_id: selectedType }
  const { data: listings, isLoading } = useListings(
    Object.keys(activeFilters).some(k => activeFilters[k as keyof ListingFilters]) ? activeFilters : undefined
  )
  const { data: types } = useListingTypes()

  return (
    <div className="max-w-2xl mx-auto px-4 py-4 space-y-4">
      {/* Search bar */}
      <div className="relative">
        <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
        <Input className="pl-9" placeholder="Search area or title..." value={search}
          onChange={e => setSearch(e.target.value)} />
      </div>

      {/* Type chips + Filter button */}
      <div className="flex gap-2 overflow-x-auto pb-1">
        <Badge variant={!selectedType ? 'default' : 'outline'} className="cursor-pointer whitespace-nowrap"
          onClick={() => setSelectedType(undefined)}>All</Badge>
        {types?.map((t: any) => (
          <Badge key={t.id} variant={selectedType === t.id ? 'default' : 'outline'}
            className="cursor-pointer whitespace-nowrap" onClick={() => setSelectedType(t.id)}>
            {t.label}
          </Badge>
        ))}
        <div className="ml-auto shrink-0">
          <FilterSheet filters={filters} onApply={setFilters} />
        </div>
      </div>

      {/* Listing grid */}
      {isLoading ? (
        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
          {Array.from({ length: 6 }).map((_, i) => (
            <Skeleton key={i} className="h-64 rounded-xl" />
          ))}
        </div>
      ) : (
        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
          {listings?.map(listing => <ListingCard key={listing.id} listing={listing} />)}
        </div>
      )}
    </div>
  )
}
```

### Listing Detail Page (`src/app/(renter)/listings/[id]/page.tsx`)

Auth required — wrap in AuthGuard.

```tsx
'use client'
import AuthGuard from '@/components/auth/AuthGuard'
import { useListing } from '@/hooks/useListings'
import { useToggleWishlist } from '@/hooks/useWishlist'
import { resolveImageUrl, formatPrice } from '@/lib/utils'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Phone, Mail, MessageCircle, Bed, Bath, Maximize2, Calendar } from 'lucide-react'
import Image from 'next/image'
import { useRouter } from 'next/navigation'
import api from '@/lib/axios'

export default function ListingDetailPage({ params }: { params: { id: string } }) {
  return (
    <AuthGuard>
      <DetailContent id={params.id} />
    </AuthGuard>
  )
}

function DetailContent({ id }: { id: string }) {
  const { data: listing, isLoading } = useListing(id)
  const router = useRouter()
  const toggle = useToggleWishlist()

  if (isLoading) return <div className="p-4">Loading...</div>
  if (!listing) return <div className="p-4">Not found</div>

  const handleContact = async () => {
    const { data } = await api.post('/chats', {
      listing_id: listing.id,
      initial_message: `Hi, I'm interested in "${listing.title}". Is it still available?`,
    })
    router.push(`/messages/${data.data.id}`)
  }

  return (
    <div className="max-w-2xl mx-auto pb-24">
      {/* Photo carousel */}
      <div className="relative h-64 bg-gray-100">
        {listing.photos.length > 0 && (
          <Image src={resolveImageUrl(listing.photos[0].url)} alt={listing.title} fill className="object-cover" />
        )}
      </div>

      <div className="px-4 py-4 space-y-4">
        <div className="flex items-start justify-between">
          <div>
            <h1 className="text-xl font-bold">{listing.title}</h1>
            <p className="text-gray-500 text-sm mt-1">{listing.area}</p>
          </div>
          <p className="text-primary font-bold text-lg">{formatPrice(listing.price)}<span className="text-xs text-gray-500">/mo</span></p>
        </div>

        {/* Quick stats */}
        <div className="flex gap-4 text-sm text-gray-600">
          {listing.beds && <span className="flex items-center gap-1"><Bed className="w-4 h-4" />{listing.beds} Beds</span>}
          {listing.baths && <span className="flex items-center gap-1"><Bath className="w-4 h-4" />{listing.baths} Baths</span>}
          {listing.size && <span className="flex items-center gap-1"><Maximize2 className="w-4 h-4" />{listing.size} sqft</span>}
        </div>

        {/* Amenities */}
        {listing.amenities.length > 0 && (
          <div className="flex flex-wrap gap-2">
            {listing.amenities.map(a => <Badge key={a.id} variant="outline">{a.label}</Badge>)}
          </div>
        )}

        {/* Description */}
        {listing.description && <p className="text-sm text-gray-700 leading-relaxed">{listing.description}</p>}

        {/* Owner info */}
        {listing.owner && (
          <div className="border rounded-xl p-3">
            <p className="font-semibold text-sm">Contact Owner</p>
            <p className="text-sm mt-1">{listing.owner_name || listing.owner.name}</p>
            {listing.owner_phone && <p className="text-sm text-gray-600">{listing.owner_phone}</p>}
          </div>
        )}
      </div>

      {/* Sticky CTA */}
      <div className="fixed bottom-0 left-0 right-0 bg-white border-t p-4 flex gap-3 max-w-2xl mx-auto">
        {listing.owner_phone && (
          <Button variant="outline" className="flex-1" asChild>
            <a href={`tel:${listing.owner_phone}`}><Phone className="w-4 h-4 mr-1" />Call</a>
          </Button>
        )}
        <Button className="flex-1" onClick={handleContact}>
          <MessageCircle className="w-4 h-4 mr-1" />Chat
        </Button>
      </div>
    </div>
  )
}
```

### Login Page (`src/app/(auth)/login/page.tsx`)

```tsx
'use client'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { z } from 'zod'
import { useLogin } from '@/hooks/useAuth'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import Link from 'next/link'

const schema = z.object({
  email: z.string().email(),
  password: z.string().min(6),
})

export default function LoginPage() {
  const { register, handleSubmit, formState: { errors } } = useForm({ resolver: zodResolver(schema) })
  const login = useLogin()

  return (
    <div className="min-h-screen flex items-center justify-center px-4">
      <div className="w-full max-w-sm space-y-6">
        <div className="text-center">
          <h1 className="text-2xl font-bold">Welcome back</h1>
          <p className="text-gray-500 text-sm mt-1">Sign in to FlatNest</p>
        </div>
        <form onSubmit={handleSubmit((d) => login.mutate(d))} className="space-y-4">
          <div>
            <Label>Email</Label>
            <Input type="email" {...register('email')} />
            {errors.email && <p className="text-red-500 text-xs mt-1">{errors.email.message}</p>}
          </div>
          <div>
            <Label>Password</Label>
            <Input type="password" {...register('password')} />
          </div>
          <Button type="submit" className="w-full" disabled={login.isPending}>
            {login.isPending ? 'Signing in...' : 'Sign In'}
          </Button>
        </form>
        {login.isError && <p className="text-red-500 text-sm text-center">Invalid email or password</p>}
        <p className="text-center text-sm text-gray-500">
          No account? <Link href="/register" className="text-primary font-medium">Sign up</Link>
        </p>
      </div>
    </div>
  )
}
```

### Owner Dashboard (`src/app/(owner)/dashboard/page.tsx`)

Auth + Owner role required.

```tsx
'use client'
import AuthGuard from '@/components/auth/AuthGuard'
import { useQuery } from '@tanstack/react-query'
import api from '@/lib/axios'
import { OwnerListing } from '@/types/api'
import { formatPrice, getThumbnail } from '@/lib/utils'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import Image from 'next/image'
import Link from 'next/link'
import { Plus } from 'lucide-react'

const STATUS_COLORS: Record<string, string> = {
  draft: 'bg-gray-100 text-gray-700',
  submitted: 'bg-yellow-100 text-yellow-700',
  approved: 'bg-green-100 text-green-700',
  rejected: 'bg-red-100 text-red-700',
  rented: 'bg-blue-100 text-blue-700',
}

export default function OwnerDashboard() {
  return (
    <AuthGuard>
      <DashboardContent />
    </AuthGuard>
  )
}

function DashboardContent() {
  const { data, isLoading } = useQuery({
    queryKey: ['owner-listings'],
    queryFn: async () => {
      const { data } = await api.get('/owner/listings')
      return data.data as OwnerListing[]
    },
  })

  return (
    <div className="max-w-2xl mx-auto px-4 py-4">
      <div className="flex items-center justify-between mb-4">
        <h1 className="text-xl font-bold">My Listings</h1>
        <Button size="sm" asChild>
          <Link href="/listings/create"><Plus className="w-4 h-4 mr-1" />New Listing</Link>
        </Button>
      </div>

      <div className="space-y-3">
        {data?.map(listing => (
          <div key={listing.id} className="border rounded-xl p-3 flex gap-3">
            <div className="relative w-20 h-20 rounded-lg overflow-hidden shrink-0">
              <Image src={getThumbnail(listing.photos)} alt={listing.title} fill className="object-cover" />
            </div>
            <div className="flex-1 min-w-0">
              <p className="font-semibold text-sm line-clamp-1">{listing.title}</p>
              <p className="text-primary text-sm font-medium">{formatPrice(listing.price)}/mo</p>
              <div className="flex items-center gap-2 mt-1">
                <span className={`text-xs px-2 py-0.5 rounded-full ${STATUS_COLORS[listing.status]}`}>
                  {listing.status_label}
                </span>
                <span className="text-xs text-gray-500">{listing.views} views</span>
                <span className="text-xs text-gray-500">{listing.inquiries} inquiries</span>
              </div>
              {listing.rejection_reason && (
                <p className="text-xs text-red-500 mt-1 line-clamp-2">{listing.rejection_reason}</p>
              )}
            </div>
            <div className="flex flex-col gap-1 shrink-0">
              <Button variant="outline" size="sm" asChild>
                <Link href={`/listings/${listing.id}/edit`}>Edit</Link>
              </Button>
            </div>
          </div>
        ))}
      </div>
    </div>
  )
}
```

---

## Part 10 — Registration Flow

### Step 1 — Register (`src/app/(auth)/register/page.tsx`)

POST `/auth/register` → save tokens → redirect to `/register/role`

### Step 2 — Role (`src/app/(auth)/register/role/page.tsx`)

Two big buttons: "I am an Owner" / "I am a Renter"
PATCH `/auth/register/details` with `{ role }` → redirect to `/register/avatar`

### Step 3 — Avatar (`src/app/(auth)/register/avatar/page.tsx`)

File input + preview → PATCH `/auth/register/avatar` (multipart) → redirect based on role:
- `owner` → `/dashboard`
- `renter` → `/`

---

## Part 11 — Create Listing Wizard (`src/app/(owner)/listings/create/page.tsx`)

5-step wizard. Use local state to track `listingId` and `currentStep`.

```
Step 1: Basic info → POST /listings → save listingId
Step 2: Photos → POST /listings/{id}/photos (multipart, photos[])
Step 3: Location → PATCH /listings/{id}/location (with cascaded geo dropdowns)
Step 4: Owner contact → PATCH /listings/{id}/owner-info
Step 5: Review → POST /listings/{id}/submit
```

Use `react-hook-form` + `zod` for each step form. Show a progress bar at the top.

---

## Part 12 — Map Page (`src/app/(renter)/map/page.tsx`)

Auth required.

```tsx
'use client'
import AuthGuard from '@/components/auth/AuthGuard'
import dynamic from 'next/dynamic'

// Leaflet must be dynamically imported (no SSR)
const NearbyMap = dynamic(() => import('@/components/map/NearbyMap'), { ssr: false })

export default function MapPage() {
  return (
    <AuthGuard>
      <NearbyMap />
    </AuthGuard>
  )
}
```

In `NearbyMap.tsx`:
- Get GPS via `navigator.geolocation.getCurrentPosition`
- Call `PATCH /user/location` with lat/lng
- Call `GET /listings/nearby?coord_x=lng&coord_y=lat&radius=5`
- Render `MapContainer` from `react-leaflet`
- Place `Marker` for each listing using `coord_x`/`coord_y`
- Clicking marker shows listing card popup with link to detail

---

## Part 13 — Chat Pages

### Chat List (`src/app/(renter)/messages/page.tsx`)

Auth required. `GET /chats` → list conversations with `other_user`, `last_message`, `unread_count`.

### Chat Detail (`src/app/(renter)/messages/[id]/page.tsx`)

Auth required.
- `GET /chats/{id}/messages` → render message bubbles
- `POST /chats/{id}/messages` → send message
- Poll messages every 5 seconds (or use WebSocket if available)
- Auto-scroll to bottom on new messages

---

## Part 14 — Navbar (`src/components/layout/Navbar.tsx`)

Show different nav based on auth state and role:

```
Guest:         Logo | Browse | Login | Sign Up
Renter:        Logo | Browse | Map | Wishlist | Messages | Bell(count) | Avatar
Owner:         Logo | Dashboard | Messages | Bell(count) | Avatar
```

On mobile: use a bottom navigation bar instead of top nav.

---

## Part 15 — App Layout (`src/app/layout.tsx`)

```tsx
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { ReactQueryDevtools } from '@tanstack/react-query-devtools'
// Wrap everything in QueryClientProvider
// Add Toaster from shadcn for toast notifications
```

---

## Part 16 — Environment Variables (`.env.local`)

```
NEXT_PUBLIC_API_URL=https://flatnest.techrealify.com/api/v1
NEXT_PUBLIC_STORAGE_URL=https://flatnest.techrealify.com
```

---

## Part 17 — Deployment to Vercel

1. Push project to GitHub
2. Go to vercel.com → New Project → Import GitHub repo
3. Set environment variables in Vercel dashboard (same as `.env.local`)
4. Deploy — every `git push` to `main` auto-deploys

Custom domain: In Vercel dashboard → Domains → Add `app.flatnest.com` (or whatever domain you choose) → Add DNS CNAME record at your registrar.

---

## Summary — What to Build

| # | Task | Auth | Priority |
|---|---|---|---|
| 1 | Backend: route + resource + controller changes | — | Critical first |
| 2 | Next.js project setup + dependencies | — | Critical |
| 3 | Axios instance + Zustand store | — | Critical |
| 4 | Home page (public browse) | Public | High |
| 5 | Login + Register (3 steps) | Public | High |
| 6 | Listing Detail page | Auth | High |
| 7 | Owner Dashboard | Owner | High |
| 8 | Create Listing Wizard (5 steps) | Owner | High |
| 9 | Filter Sheet + AuthModal gate | Mixed | High |
| 10 | Wishlist page | Renter | Medium |
| 11 | Map / Nearby page | Renter | Medium |
| 12 | Chat List + Chat Detail | Both | Medium |
| 13 | Notifications page | Both | Medium |
| 14 | Profile / Settings | Both | Low |
| 15 | Vercel deployment | — | Final |
