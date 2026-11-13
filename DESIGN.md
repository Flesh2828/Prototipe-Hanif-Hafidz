# DESIGN.md -- ui kopikita

<!-- extraction-meta
source: Figma file "ui kopikita"
scope: entire file (1 page)
date: 2026-11-13
nodes-scanned: 3498
confidence: { extracted: 100%, inferred: 0%, known: 0% }
-->

## 1. Identity

**In one line:** A design system using Plus Jakarta Sans, Liberation Mono, IPAGothic, FreeSerif, FreeSans with 66 unique colors extracted directly from Figma.

**Signature Techniques:**
- Consistent auto-layout spacing system
- Rounded shape language (12px+ radii)
- Layered shadow system for depth

## 2. Structure

High-level composition of the design, extracted from Figma pages and top-level frames. Each entry shows the frame name, type, dimensions, and auto-layout direction.

### Page: Page 1

_6 top-level frame(s)_

- **KopiKita - Multi-Outlet POS & Management System** · `FRAME` · 1280×1318 · vertical stack · 2 children
  - **Header - TOP NAV BAR (Shared Components JSON Anchor)** · `FRAME` · 1280×56 · horizontal row, gap 16.239999771118164px, padding 0/20/0/20px · 2 children
    - **Container** · `FRAME` · 503×24 · horizontal row, gap 24px · 2 children
      - **Container** · `FRAME` · 149×24 · horizontal row, gap 8px · 2 children
        - _...and 2 more_
      - **Top Nav Links with Semantic Active State mapping:margin** · `FRAME` · 330×24 · vertical stack, padding 0/0/0/16px · 1 children
        - _...and 1 more_
    - **Trailing Action Cluster & Global Search** · `FRAME` · 697×36 · horizontal row, gap 16px · 4 children
      - **Container** · `FRAME` · 256×36 · vertical stack · 2 children
        - _...and 2 more_
      - **VerticalBorder** · `FRAME` · 106×29 · horizontal row, gap 6px, padding 0/12/0/0px · 3 children
        - _...and 3 more_
      - **Action Status Badges** · `FRAME` · 247×36 · horizontal row, gap 8px · 2 children
        - _...and 2 more_
      - **Barista Alex Headshot Avatar** · `FRAME` · 40×32 · horizontal row, padding 0/0/0/8px · 1 children
        - _...and 1 more_
  - **3-PANE APPLICATION WORKSPACE** · `FRAME` · 1280×1262 · horizontal row · 3 children
    - **Aside - PANE 1: LEFT NAVIGATION RAIL / SIDEBAR (260px Fixed, Rich Espresso)** · `FRAME` · 256×744 · vertical stack · 4 children
      - **Aside - PANE 1: LEFT NAVIGATION RAIL / SIDEBAR (260px Fixed, Rich Espresso):shadow** · `RECTANGLE` · 256×744
      - **Outlet & Branch Switcher** · `FRAME` · 255×120 · vertical stack, gap 10px, padding 12px · 2 children
        - _...and 2 more_
      - **Main Navigation Accordions / List** · `FRAME` · 255×471 · vertical stack, gap 16px, padding 8/0/8/0px · 2 children
        - _...and 2 more_
      - **Sidebar Footer (Shift Info, Cash Drawer, Actions)** · `FRAME` · 255×153 · vertical stack, gap 8px, padding 12px · 3 children
        - _...and 3 more_
    - **Main - PANE 2: CENTRAL WORK CANVAS (Dynamic Menu & Live Stock HPP)** · `FRAME` · 644×1262 · vertical stack · 2 children
      - **Section - Sub-header Top Controls & Functional Pills** · `FRAME` · 644×147 · vertical stack, gap 12px, padding 12/20/12/20px · 2 children
        - _...and 2 more_
      - **Scrollable Product Catalog Grid & Integrated HPP Bar** · `FRAME` · 644×1115 · vertical stack, gap 16px, padding 20px · 3 children
        - _...and 3 more_
    - **Aside - PANE 3: RIGHT SIDEBAR - ACTIVE CHECKOUT & ORDER SUMMARY (380px Fixed)** · `FRAME` · 380×744 · vertical stack · 4 children
      - **Aside - PANE 3: RIGHT SIDEBAR - ACTIVE CHECKOUT & ORDER SUMMARY (380px Fixed):shadow** · `RECTANGLE` · 380×744
      - **Order Header Section** · `FRAME` · 379×103 · vertical stack, gap 12px, padding 16px · 2 children
        - _...and 2 more_
      - **Scrollable Order Items List** · `FRAME` · 379×253 · 3 children
        - _...and 3 more_
      - **Financial Calculation & HPP Profit Indicator** · `FRAME` · 379×388 · vertical stack, gap 8px, padding 16px · 5 children
        - _...and 5 more_

- **KopiKita - Critical Stock & Recipe Batch PO Modal** · `FRAME` · 1280×1229 · vertical stack · 2 children
  - **DESKTOP POS BASE CANVAS (Background Layer behind the Active Management Modal)** · `FRAME` · 1280×1024 · vertical stack · 2 children
    - **Header - Top Bar Component** · `FRAME` · 1280×56 · horizontal row, gap 601.760009765625px, padding 0/20/0/20px · 2 children
      - **Container** · `FRAME` · 445×24 · horizontal row, gap 24px · 2 children
        - _...and 2 more_
      - **Container** · `FRAME` · 194×30 · horizontal row, gap 12px · 2 children
        - _...and 2 more_
    - **Container** · `FRAME` · 1280×968 · horizontal row · 3 children
      - **Aside - Dummy Side Nav** · `FRAME` · 256×968 · vertical stack, padding 16px · 1 children
        - _...and 1 more_
      - **Main - Central dummy grid** · `FRAME` · 640×968 · horizontal row, gap 16px, padding 24px · 4 children
        - _...and 4 more_
      - **Aside - Dummy ticket ledger** · `RECTANGLE` · 384×968
  - **MODAL BACKDROP (Tier 3 Elevation: Rich Espresso Overlay)** · `FRAME` · 1280×1229 · horizontal row, padding 24px · 1 children
    - **MODAL CONTAINER (Tier 3: Lifted Surface with warm micro-border)** · `FRAME` · 1232×920 · vertical stack · 3 children
      - **MODAL TOP BAR / ALERT BANNER** · `FRAME` · 1230×89 · horizontal row, padding 12/20/12/20px · 2 children
        - _...and 2 more_
      - **FILTER & VIEW CONTROLS** · `FRAME` · 1230×71 · horizontal row, padding 10/20/10/20px · 2 children
        - _...and 2 more_
      - **MAIN MODAL BODY (Grid: 2/3 Main Procurement Table + 1/3 HPP Engine & Dispatch)** · `FRAME` · 1230×758 · free · 2 children
        - _...and 2 more_

- **KopiKita - Kitchen Ticket & KOT Expediter Display** · `FRAME` · 1280×1024 · horizontal row · 2 children
  - **Aside** · `FRAME` · 256×1024 · vertical stack · 5 children
    - **Aside:shadow** · `RECTANGLE` · 256×1024
    - **Brand / Outlet Header** · `FRAME` · 255×155 · vertical stack, gap 16px, padding 20px · 2 children
      - **Container** · `FRAME` · 215×58 · horizontal row, gap 12px · 2 children
        - _...and 2 more_
      - **Button - Quick Status Action** · `FRAME` · 215×40 · horizontal row, gap 0.029999999329447746px, padding 8/12/8/12px · 2 children
        - _...and 2 more_
    - **Tab Navigation (Kitchen Tickets is Active)** · `FRAME` · 255×665 · vertical stack, gap 4px, padding 16/12/16/12px · 6 children
      - **Link - 1. POS & Fast Billing** · `FRAME` · 231×38 · horizontal row, gap 42.59000015258789px, padding 10/12/10/12px · 2 children
        - _...and 2 more_
      - **Link - 2. Held Orders** · `FRAME` · 231×40 · horizontal row, gap 75.95999908447266px, padding 10/12/10/12px · 2 children
        - _...and 2 more_
      - **Link - 3. Kitchen Tickets (ACTIVE)** · `FRAME` · 231×42 · horizontal row, gap 14.359999656677246px, padding 10/11.989999771118164/10/12px · 2 children
        - _...and 2 more_
      - **Link - 4. Live Inventory** · `FRAME` · 231×38 · horizontal row, padding 10/12/10/12px · 1 children
        - _...and 1 more_
      - **Link - 5. Multi-Outlet P&L** · `FRAME` · 231×38 · horizontal row, padding 10/12/10/12px · 1 children
        - _...and 1 more_
      - **Link - 6. Settings & RBAC** · `FRAME` · 231×38 · horizontal row, padding 10/12/10/12px · 1 children
        - _...and 1 more_
    - **Shift Info & Station Assignees:margin** · `FRAME` · 255×108 · vertical stack, padding 8/12/8/12px · 1 children
      - **Shift Info & Station Assignees** · `FRAME` · 231×92 · vertical stack, gap 8px, padding 12px · 2 children
        - _...and 2 more_
    - **Footer Controls** · `FRAME` · 255×97 · vertical stack, gap 4px, padding 12px · 2 children
      - **Link** · `FRAME` · 231×34 · horizontal row, gap 12px, padding 8/12/8/12px · 2 children
        - _...and 2 more_
      - **Link** · `FRAME` · 231×34 · horizontal row, gap 12px, padding 8/12/8/12px · 2 children
        - _...and 2 more_
  - **Background** · `FRAME` · 1024×1024 · 4 children
    - **Section - Operational Metrics & Station Filter Strip** · `FRAME` · 1024×144 · vertical stack, gap 10px, padding 10/20/10/20px · 2 children
      - **Container** · `FRAME` · 984×78 · vertical stack, gap 12px · 2 children
        - _...and 2 more_
      - **Station Filter Pills & Channel Switchers** · `FRAME` · 984×35 · horizontal row, gap 7.96999979019165px, padding 8/0/0/0px · 2 children
        - _...and 2 more_
    - **Main** · `FRAME` · 1024×685 · 5 children
      - **Article** · `FRAME` · 320×506 · vertical stack · 4 children
        - _...and 4 more_
      - **Article** · `FRAME` · 320×461 · vertical stack · 4 children
        - _...and 4 more_
      - **Article** · `FRAME` · 320×378 · vertical stack · 4 children
        - _...and 4 more_
      - **Article** · `FRAME` · 320×394 · vertical stack · 4 children
        - _...and 4 more_
      - **Article** · `FRAME` · 320×279 · vertical stack · 4 children
        - _...and 4 more_
    - **Footer** · `FRAME` · 1024×119 · vertical stack, padding 10/20/10/20px · 2 children
      - **Footer:shadow** · `RECTANGLE` · 1024×119
      - **Container** · `FRAME` · 984×98 · vertical stack, gap 16px · 2 children
        - _...and 2 more_
    - **Header - TopNavBar (Shared Component)** · `FRAME` · 1024×56 · horizontal row, padding 0/20/0/20px · 2 children
      - **Left: Logo & Breadcrumbs** · `FRAME` · 594×48 · horizontal row, gap 24px · 2 children
        - _...and 2 more_
      - **Right: Action Icons, Offline Sync, Register & Profile** · `FRAME` · 382×70 · horizontal row, gap 12px · 4 children
        - _...and 4 more_

- **KopiKita - User Management & RBAC Permission Matrix** · `FRAME` · 1280×499 · vertical stack · 2 children
  - **Header - TopNavBar (Shared Component JSON Blueprint)** · `FRAME` · 1280×56 · horizontal row, padding 0/16.690000534057617/0/20px · 2 children
    - **Left: Brand Logo & Breadcrumb Navigation** · `FRAME` · 570×48 · horizontal row, gap 24px · 3 children
      - **Container** · `FRAME` · 120×48 · horizontal row, gap 10px · 2 children
        - _...and 2 more_
      - **Navigation Links from Shared JSON** · `FRAME` · 189×28 · horizontal row, gap 24px, padding 0/0/0/24px · 3 children
        - _...and 3 more_
      - **Context Breadcrumb Pill** · `FRAME` · 213×38 · horizontal row, gap 8px, padding 4/12/4/12px · 6 children
        - _...and 6 more_
    - **Right: Search, Trailing Actions, Quick Statuses** · `FRAME` · 668×38 · horizontal row, gap 16px · 5 children
      - **Search Input on Right** · `FRAME` · 255×32 · vertical stack · 2 children
        - _...and 2 more_
      - **Offline Sync Badge** · `FRAME` · 93×38 · horizontal row, gap 6px, padding 4/10/4/10px · 2 children
        - _...and 2 more_
      - **Register #01 Badge** · `FRAME` · 99×38 · horizontal row, gap 6px, padding 4/10/4/10px · 2 children
        - _...and 2 more_
      - **Trailing Icon Actions** · `FRAME` · 117×32 · horizontal row, gap 4px, padding 0/0/0/12px · 3 children
        - _...and 3 more_
      - **Profile Avatar** · `FRAME` · 41×32 · horizontal row, padding 0/0/0/8px · 1 children
        - _...and 1 more_
  - **Main Body Shell: Left SideNavBar + Central Fluid Canvas** · `FRAME` · 1280×443 · horizontal row · 2 children
    - **Aside - SideNavBar (Shared Component JSON Blueprint)** · `FRAME` · 256×443 · vertical stack · 3 children
      - **Aside - SideNavBar (Shared Component JSON Blueprint):shadow** · `RECTANGLE` · 256×443
      - **Upper Section: Outlet Branding & Nav Links** · `FRAME` · 255×303 · vertical stack, padding 12/0/0/0px · 2 children
        - _...and 2 more_
      - **Lower Section: Drawer CTA & Footer items** · `FRAME` · 255×129 · vertical stack, gap 8px, padding 12px · 2 children
        - _...and 2 more_
    - **Main Content Canvas (2-Column Bento/Matrix Architecture)** · `FRAME` · 1024×443 · vertical stack · 3 children
      - **Top Action & KPI Security Banner** · `FRAME` · 1024×230 · vertical stack, gap 12px, padding 16/24/16/24px · 2 children
        - _...and 2 more_
      - **Main Workspace: 2-Column Split (Left: Staff Directory, Right: RBAC Matrix)** · `FRAME` · 1024×101 · horizontal row, gap 16px, padding 16px · 2 children
        - _...and 2 more_
      - **Footer - Bottom Floating Drawer / Security Audit Notification Bar** · `FRAME` · 1024×113 · vertical stack, gap 16px, padding 12/24/12/24px · 2 children
        - _...and 2 more_

- **KopiKita - Multi-Outlet Profit & Loss (P&L) Dashboard** · `FRAME` · 1280×1786 · vertical stack · 2 children
  - **Header - TOP NAV BAR (Shared Component Anchor)** · `FRAME` · 1280×56 · horizontal row, gap 46.09000015258789px, padding 0/20/0/20px · 2 children
    - **Container** · `FRAME` · 498×24 · horizontal row, gap 24px · 2 children
      - **Container** · `FRAME` · 151×24 · horizontal row, gap 10px · 2 children
        - _...and 2 more_
      - **Navigation Links:margin** · `FRAME` · 323×24 · vertical stack, padding 0/0/0/16px · 1 children
        - _...and 1 more_
    - **Trailing Actions & Search** · `FRAME` · 668×32 · horizontal row, gap 12px · 5 children
      - **Search Bar on Right** · `FRAME` · 256×30 · vertical stack · 2 children
        - _...and 2 more_
      - **Button - Action Pill: Trailing Primary Action** · `FRAME` · 120×32 · horizontal row, gap 6px, padding 6/12/6/12px · 2 children
        - _...and 2 more_
      - **Trailing Secondary Action** · `FRAME` · 105×24 · horizontal row, gap 3.990000009536743px, padding 4/10/4/10px · 2 children
        - _...and 2 more_
      - **Trailing Icon Actions** · `FRAME` · 98×29 · horizontal row, gap 4px, padding 0/0/0/8px · 3 children
        - _...and 3 more_
      - **User Profile** · `FRAME` · 41×32 · horizontal row, padding 0/0/0/8px · 1 children
        - _...and 1 more_
  - **MAIN VIEWPORT CONTAINER** · `FRAME` · 1280×1730 · 2 children
    - **Aside - SIDE NAV BAR (Shared Component Anchor)** · `FRAME` · 256×1730 · vertical stack, gap 1158px · 3 children
      - **Aside - SIDE NAV BAR (Shared Component Anchor):shadow** · `RECTANGLE` · 256×1730
      - **Top Section** · `FRAME` · 255×333 · vertical stack, gap 16px, padding 16/0/0/0px · 2 children
        - _...and 2 more_
      - **Bottom Drawer CTA & Footer Tabs** · `FRAME` · 255×149 · vertical stack, gap 8px, padding 12px · 2 children
        - _...and 2 more_
    - **Main - DASHBOARD CONTENT CANVAS** · `FRAME` · 1042×1730 · 3 children
      - **Section - Outlet Filter Ribbon** · `FRAME` · 1042×49 · vertical stack, padding 10/24/10/24px · 1 children
        - _...and 1 more_
      - **Main Scrollable Dashboard Content** · `FRAME` · 1042×1526 · vertical stack, gap 24px, padding 24px · 2 children
        - _...and 2 more_
      - **Section - Subheader / Filter Controls Bar** · `FRAME` · 1042×141 · vertical stack, gap 16px, padding 16/24/16/24px · 2 children
        - _...and 2 more_

- **KopiKita - Recipe & Menu Engineering (Per-Cup HPP)** · `FRAME` · 1280×1485 · vertical stack · 1 children
  - **Container** · `FRAME` · 1280×1485 · horizontal row · 2 children
    - **Aside - 1. LEFT NAVIGATION SIDEBAR (Shared Component: SideNavBar)** · `FRAME` · 256×1024 · vertical stack · 4 children
      - **Aside - 1. LEFT NAVIGATION SIDEBAR (Shared Component: SideNavBar):shadow** · `RECTANGLE` · 256×1024
      - **Sidebar Header** · `FRAME` · 255×181 · vertical stack, gap 16px, padding 20px · 2 children
        - _...and 2 more_
      - **Navigation Tabs** · `FRAME` · 255×746 · vertical stack, gap 4px, padding 12px · 7 children
        - _...and 7 more_
      - **Sidebar Footer** · `FRAME` · 255×97 · vertical stack, gap 8px, padding 12px · 2 children
        - _...and 2 more_
    - **MAIN APP CANVAS (Header + Split Content)** · `FRAME` · 1024×1485 · vertical stack · 3 children
      - **Header - TOP NAV BAR (Shared Component: TopNavBar style)** · `FRAME` · 1024×56 · horizontal row, padding 0/20/0/20px · 2 children
        - _...and 2 more_
      - **Section - SUB-HEADER / ACTIONS STRIP** · `FRAME` · 1024×117 · vertical stack, gap 16px, padding 12/20/12/20px · 2 children
        - _...and 2 more_
      - **Main - MAIN SCROLLABLE VIEWPORT** · `FRAME` · 1024×1312 · vertical stack, gap 16px, padding 20px · 2 children
        - _...and 2 more_

## 3. Color

### Palette
| Token | Value | Role | Usage | Similar | Source |
|-------|-------|------|-------|---------|--------|
| `accent` | `#d4c3bc` | accent | 338× | — | node |
| `background` | `#ffffff` | background | 265× | — | node |
| `text-secondary` | `#50443f` | text-secondary | 226× | — | node |
| `text-primary` | `#250f03` | text-primary | 216× | `#221100` | node |
| `text-tertiary` | `#82746e` | text-tertiary | 166× | — | node |
| `accent-alt` | `#7d562d` | accent | 133× | `#7a532a` | node |
| `text-primary-alt` | `#1c1c18` | text-primary | 124× | — | node |
| `accent-3` | `#ffdcbd` | accent | 121× | `#ffddba` | node |
| `accent-4` | `#af8874` | accent | 98× | — | node |
| `surface` | `#f0eee8` | surface | 84× | — | node |
| `accent-5` | `#ba1a1a` | accent | 71× | `#b91c1c` | node |
| `accent-6` | `#2e7d32` | accent | 52× | — | node |
| `accent-7` | `#3d2314` | accent | 51× | — | node |
| `surface-alt` | `#ebe8e2` | surface | 38× | — | node |
| `surface-3` | `#f6f3ed` | surface | 38× | — | node |
| `accent-8` | `#065f46` | accent | 37× | — | node |
| `accent-9` | `#d1fae5` | accent | 35× | — | node |
| `surface-4` | `#e5e2dc` | surface | 31× | — | node |
| `accent-10` | `#ffca98` | accent | 23× | — | node |
| `background-alt` | `#fcf9f3` | background | 23× | — | node |

_The **Similar** column lists hexes that were visually indistinguishable (Δ < 12) and collapsed into the canonical token. Use the canonical token in code; treat the similar values as the same intent._

## 4. Typography

### Fonts
- **Plus Jakarta Sans**
- **Liberation Mono**
- **IPAGothic**
- **FreeSerif**
- **FreeSans**

### Scale
| Role | Token | Size | Weight | Line Height | Letter Spacing | Source |
|------|-------|------|--------|-------------|----------------|--------|
| Display | `display` | 24px | 700 | 28px | normal | node |
| H1 | `h1` | 22px | 600 | 28px | normal | node |
| Body lg (600) | `body-lg-semibold` | 18px | 600 | 24px | normal | node |
| Body lg (700) | `body-lg-bold` | 18px | 700 | 24px | 0px | node |
| Body | `body` | 16px | 400 | 24px | normal | node |
| Body (700) | `body-bold` | 16px | 700 | 24px | normal | node |
| Body (700) | `body-bold-2` | 15px | 700 | 22.5px | normal | node |
| Body sm | `body-sm` | 14px | 400 | 20px | normal | node |
| Body sm (500) | `body-sm-medium` | 13px | 500 | 18px | normal | node |
| Body sm (600) | `body-sm-semibold` | 14px | 600 | 20px | normal | node |
| Body sm (600) | `body-sm-semibold-2` | 13px | 600 | 18px | normal | node |
| Body sm (700) | `body-sm-bold` | 14px | 700 | 20px | normal | node |
| Body sm (700) | `body-sm-bold-2` | 13px | 700 | 18px | normal | node |
| Caption | `caption` | 12px | 400 | auto | normal | node |
| Caption | `caption-2` | 11px | 400 | 20px | normal | node |
| Caption | `caption-3` | 10px | 400 | 15px | normal | node |
| Caption (500) | `caption-medium` | 12px | 500 | 16px | normal | node |
| Caption (500) | `caption-medium-2` | 11px | 500 | 16.5px | normal | node |
| Caption (500) | `caption-medium-3` | 10px | 500 | 15px | normal | node |
| Caption (600) | `caption-semibold` | 12px | 600 | 16px | normal | node |
| Caption (600) | `caption-semibold-2` | 11px | 600 | 14px | normal | node |
| Caption (600) | `caption-semibold-3` | 10px | 600 | 18px | normal | node |
| Caption (700) | `caption-bold` | 12px | 700 | 16px | normal | node |
| Caption (700) | `caption-bold-2` | 11px | 700 | 14px | normal | node |

## 5. Spacing & Layout

### Base Unit
Values found: 0, 0.5, 1, 1.5, 2, 2.5, 3, 3.5, 4, 4.5, 5, 5.5, 6, 6.5, 7, 7.5, 8, 8.5, 9, 9.5, 10, 10.5, 11, 11.5, 12, 12.5, 13, 13.5, 14, 14.5, 15, 15.5, 16, 16.5, 17, 17.5, 18, 18.5, 19, 19.5, 20, 21, 21.5, 22, 23, 23.5, 24, 25, 25.5, 26, 26.5, 27, 28, 29, 29.5, 30, 30.5, 31.5, 32, 32.5, 33, 33.5, 34, 35, 35.5, 36, 37, 38, 38.5, 39, 39.5, 40, 40.5, 41, 42, 43, 43.5, 44, 45.5, 46, 46.5, 47.5, 48, 48.5, 49, 49.5, 50, 50.5, 52.5, 53, 53.5, 54, 54.5, 55, 56, 56.5, 57, 58, 58.5, 59, 59.5, 60, 60.5, 61, 61.5, 62, 63, 63.5, 64, 66, 67, 68, 69, 70, 70.5, 71, 73.5, 74, 75, 76, 77, 78, 81, 88, 90.5, 96, 98, 103, 109, 110, 118, 121, 122, 129, 131, 137, 148, 149, 166, 185, 186, 187, 209, 215, 218, 251, 383, 602, 1158

### Border Radius
| Token | Value | Usage Count |
|-------|-------|-------------|
| `radius-sm-3` | 2px | 3 |
| `radius-sm-2` | 4px | 118 |
| `radius-sm` | 6px | 3 |
| `radius-md` | 8px | 215 |
| `radius-lg-2` | 12px | 64 |
| `radius-full` | 9999px | 185 |

## 6. Depth & Motion

### Elevation
| Token | Shadow | Source |
|-------|--------|--------|
| `elevation-1` | `0px 0px 0px 2px rgba(255, 255, 255, 1)` | node |
| `elevation-2` | `0px 1px 2px 0px rgba(0, 0, 0, 0.05)` | node |
| `elevation-3` | `0px 1px 2px -1px rgba(0, 0, 0, 0.1), 0px 1px 3px 0px rgba(0, 0, 0, 0.1)` | node |
| `elevation-4` | `0px 2px 4px -2px rgba(0, 0, 0, 0.1), 0px 4px 6px -1px rgba(0, 0, 0, 0.1)` | node |
| `elevation-5` | `0px 2px 4px 0px rgba(61, 35, 20, 0.04)` | node |
| `elevation-6` | `inset 0px 2px 4px 0px rgba(0, 0, 0, 0.05)` | node |
| `elevation-7` | `0px 4px 6px -4px rgba(0, 0, 0, 0.1), 0px 10px 15px -3px rgba(0, 0, 0, 0.1)` | node |
| `elevation-8` | `0px 6px 16px 0px rgba(61, 35, 20, 0.08)` | node |
| `elevation-9` | `0px 25px 50px -12px rgba(0, 0, 0, 0.25)` | node |

## 7. Components

No named components detected.

## 8. States

State tokens should be derived from the base palette above. Recommended mappings:

| State | Treatment |
|-------|-----------|
| Hover | Lighten/darken accent by 10% |
| Focus | 2px ring using accent color with 30% opacity |
| Disabled | 40% opacity, no pointer events |
| Error | Use danger color for border and text |

## 9. Rules

### Do
- Use `#ffffff` (`background`) as the page background
- Use `#250f03` (`text-primary`) for primary text
- Use `#d4c3bc` (`accent`) as the primary accent color
- Keep border-radius consistent: 2px, 4px, 6px, 8px, 12px, 9999px
- Use the spacing scale above for all padding and margins
- Maintain the type scale hierarchy for visual rhythm

### Don't
- Don't use colors outside the extracted palette
- Don't mix font families arbitrarily
- Don't use arbitrary spacing values outside the scale
- Don't flatten the shadow system to single-layer shadows

## 10. Extending this system

This file captures the visual language of one screen (or a small set). Most products grow from a landing page into a full app — auth, dashboard, settings, marketing pages, emails. Reuse this document as the canonical reference so new screens stay coherent.

### How to reuse this DESIGN.md
1. **Treat it as the source of truth.** Commit it at the repo root. Any new page or component should be built from the tokens above, not re-invented.
2. **Feed it to your AI coding tool.** Paste this file (or include it via `@DESIGN.md`) when prompting Copilot / Cursor / Claude to generate new pages. The model will reuse the exact tokens instead of inventing new ones.
3. **Re-run this plugin** whenever the Figma file changes substantially and diff the output. The diff itself is your design-system changelog.
4. **Promote tokens to code.** Mirror the palette, type scale, spacing, and radii into CSS variables, a Tailwind config, or a tokens file. Reference them by name in components — never hardcode hex/px values.

### Adding a new screen
- Start from the **Identity** statement above — the new screen must read as the same product.
- Pick layouts from existing **Structure** patterns (same containers, same gaps, same padding rhythm) before introducing new ones.
- Use only the existing spacing scale (0px, 0.5px, 1px, 1.5px, 2px, 2.5px, 3px, 3.5px, 4px, 4.5px, 5px, 5.5px, 6px, 6.5px, 7px, 7.5px, 8px, 8.5px, 9px, 9.5px, 10px, 10.5px, 11px, 11.5px, 12px, 12.5px, 13px, 13.5px, 14px, 14.5px, 15px, 15.5px, 16px, 16.5px, 17px, 17.5px, 18px, 18.5px, 19px, 19.5px, 20px, 21px, 21.5px, 22px, 23px, 23.5px, 24px, 25px, 25.5px, 26px, 26.5px, 27px, 28px, 29px, 29.5px, 30px, 30.5px, 31.5px, 32px, 32.5px, 33px, 33.5px, 34px, 35px, 35.5px, 36px, 37px, 38px, 38.5px, 39px, 39.5px, 40px, 40.5px, 41px, 42px, 43px, 43.5px, 44px, 45.5px, 46px, 46.5px, 47.5px, 48px, 48.5px, 49px, 49.5px, 50px, 50.5px, 52.5px, 53px, 53.5px, 54px, 54.5px, 55px, 56px, 56.5px, 57px, 58px, 58.5px, 59px, 59.5px, 60px, 60.5px, 61px, 61.5px, 62px, 63px, 63.5px, 64px, 66px, 67px, 68px, 69px, 70px, 70.5px, 71px, 73.5px, 74px, 75px, 76px, 77px, 78px, 81px, 88px, 90.5px, 96px, 98px, 103px, 109px, 110px, 118px, 121px, 122px, 129px, 131px, 137px, 148px, 149px, 166px, 185px, 186px, 187px, 209px, 215px, 218px, 251px, 383px, 602px, 1158px). If you need a new value, add it here first so the next person knows it's allowed.
- Reuse the same **States** treatments (hover, focus, disabled, error). Consistency across screens is what makes states feel intentional.

### When to add a new token vs reuse
| Situation | Action |
|-----------|--------|
| Need a color that's a tint/shade of an existing one | Reuse + adjust opacity, don't add a new hex |
| Need a font size between two existing steps | Pick the closer existing step; resist filling the gap |
| Need a one-off spacing value | Round to the nearest scale value first |
| Need a genuinely new semantic role (e.g. `info`, `brand-2`) | Add it here with a clear role + confidence note |
| Need a new component pattern used 3+ times | Promote to the Components section |

### Page types likely to come next
If this design is a landing page, here are common follow-on surfaces and what to inherit:

| Surface | Inherit | Likely new tokens |
|---------|---------|-------------------|
| Auth (sign in / sign up) | Inputs, buttons, type scale, background | Form validation states, link color |
| Dashboard / app shell | Spacing, radii, shadows, nav patterns | Sidebar widths, data-density type step, table row heights |
| Settings | Inputs, buttons, type scale | Section dividers, toggle component, danger-zone treatment |
| Marketing / content pages | Identity, type scale, hero patterns | Long-form body width, blockquote, code block (if relevant) |
| Empty / error / 404 states | Type scale, illustration tone, CTA pattern | Illustration sizing tokens |
| Transactional emails | Color palette (with email-safe fallbacks), type scale | Email-safe font stack, fixed widths (600px) |

### Versioning
- Bump a header (`<!-- version: X.Y -->`) when the palette, type scale, or spacing scale changes — those are breaking.
- Non-breaking additions (a new component, a new shadow level) are minor.
- Keep this file in the same PR as the code change that introduces or consumes the new token, so design and code never drift.

## 11. Machine-readable tokens

The block below is the canonical token map. Reference this when generating code or syncing to CSS variables / Tailwind config. It mirrors the tables above but is unambiguous and parseable.

```json design-tokens
{
  "$schema": "design-tokens.v1",
  "meta": {
    "source": "ui kopikita",
    "generated": "2026-11-13"
  },
  "color": {
    "accent": "#d4c3bc",
    "background": "#ffffff",
    "text-secondary": "#50443f",
    "text-primary": "#250f03",
    "text-tertiary": "#82746e",
    "accent-alt": "#7d562d",
    "text-primary-alt": "#1c1c18",
    "accent-3": "#ffdcbd",
    "accent-4": "#af8874",
    "surface": "#f0eee8",
    "accent-5": "#ba1a1a",
    "accent-6": "#2e7d32",
    "accent-7": "#3d2314",
    "surface-alt": "#ebe8e2",
    "surface-3": "#f6f3ed",
    "accent-8": "#065f46",
    "accent-9": "#d1fae5",
    "surface-4": "#e5e2dc",
    "accent-10": "#ffca98",
    "background-alt": "#fcf9f3",
    "accent-11": "#047857",
    "accent-12": "#78350f",
    "surface-5": "#ffdad6",
    "accent-13": "#6ee7b7",
    "text-primary-3": "#2c1600",
    "accent-14": "#93000a",
    "text-primary-4": "#000000",
    "accent-15": "#3e2300",
    "background-3": "#fffbeb",
    "accent-16": "#10b981",
    "accent-17": "#fef3c7",
    "accent-18": "#fde68a",
    "accent-19": "#064e3b",
    "accent-20": "#34d399",
    "accent-21": "#b45309",
    "background-4": "#ecfdf5",
    "text-tertiary-alt": "#6b7280",
    "accent-22": "#d4a373",
    "accent-23": "#a7f3d0",
    "surface-6": "#e8f5e9",
    "surface-7": "#fee2e2",
    "accent-24": "#f0bd8b",
    "accent-25": "#92400e",
    "accent-26": "#c58940",
    "accent-27": "#e9bda7",
    "accent-28": "#fecaca",
    "accent-29": "#1e3a8a",
    "accent-30": "#7f1d1d",
    "accent-31": "#991b1b",
    "background-5": "#fef2f2",
    "accent-32": "#fdb96b",
    "accent-33": "#f59e0b",
    "accent-34": "#bf843c",
    "accent-35": "#059669",
    "accent-36": "#bfdbfe",
    "accent-37": "#1d4ed8",
    "accent-38": "#fcd34d",
    "accent-39": "#93c5fd",
    "accent-40": "#fca5a5",
    "background-6": "#eff6ff",
    "surface-8": "#f4ece1",
    "surface-9": "#dbeafe"
  },
  "typography": {
    "display": {
      "fontFamily": "Plus Jakarta Sans",
      "fontSize": 24,
      "fontWeight": 700,
      "lineHeight": "28px",
      "letterSpacing": "normal"
    },
    "h1": {
      "fontFamily": "Plus Jakarta Sans",
      "fontSize": 22,
      "fontWeight": 600,
      "lineHeight": "28px",
      "letterSpacing": "normal"
    },
    "body-lg-semibold": {
      "fontFamily": "Plus Jakarta Sans",
      "fontSize": 18,
      "fontWeight": 600,
      "lineHeight": "24px",
      "letterSpacing": "normal"
    },
    "body-lg-bold": {
      "fontFamily": "Plus Jakarta Sans",
      "fontSize": 18,
      "fontWeight": 700,
      "lineHeight": "24px",
      "letterSpacing": "0px"
    },
    "body": {
      "fontFamily": "Plus Jakarta Sans",
      "fontSize": 16,
      "fontWeight": 400,
      "lineHeight": "24px",
      "letterSpacing": "normal"
    },
    "body-bold": {
      "fontFamily": "Plus Jakarta Sans",
      "fontSize": 16,
      "fontWeight": 700,
      "lineHeight": "24px",
      "letterSpacing": "normal"
    },
    "body-bold-2": {
      "fontFamily": "Plus Jakarta Sans",
      "fontSize": 15,
      "fontWeight": 700,
      "lineHeight": "22.5px",
      "letterSpacing": "normal"
    },
    "body-sm": {
      "fontFamily": "Plus Jakarta Sans",
      "fontSize": 14,
      "fontWeight": 400,
      "lineHeight": "20px",
      "letterSpacing": "normal"
    },
    "body-sm-medium": {
      "fontFamily": "Plus Jakarta Sans",
      "fontSize": 13,
      "fontWeight": 500,
      "lineHeight": "18px",
      "letterSpacing": "normal"
    },
    "body-sm-semibold": {
      "fontFamily": "Plus Jakarta Sans",
      "fontSize": 14,
      "fontWeight": 600,
      "lineHeight": "20px",
      "letterSpacing": "normal"
    },
    "body-sm-semibold-2": {
      "fontFamily": "Plus Jakarta Sans",
      "fontSize": 13,
      "fontWeight": 600,
      "lineHeight": "18px",
      "letterSpacing": "normal"
    },
    "body-sm-bold": {
      "fontFamily": "Plus Jakarta Sans",
      "fontSize": 14,
      "fontWeight": 700,
      "lineHeight": "20px",
      "letterSpacing": "normal"
    },
    "body-sm-bold-2": {
      "fontFamily": "Plus Jakarta Sans",
      "fontSize": 13,
      "fontWeight": 700,
      "lineHeight": "18px",
      "letterSpacing": "normal"
    },
    "caption": {
      "fontFamily": "Plus Jakarta Sans",
      "fontSize": 12,
      "fontWeight": 400,
      "lineHeight": "auto",
      "letterSpacing": "normal"
    },
    "caption-2": {
      "fontFamily": "Plus Jakarta Sans",
      "fontSize": 11,
      "fontWeight": 400,
      "lineHeight": "20px",
      "letterSpacing": "normal"
    },
    "caption-3": {
      "fontFamily": "Plus Jakarta Sans",
      "fontSize": 10,
      "fontWeight": 400,
      "lineHeight": "15px",
      "letterSpacing": "normal"
    },
    "caption-medium": {
      "fontFamily": "Plus Jakarta Sans",
      "fontSize": 12,
      "fontWeight": 500,
      "lineHeight": "16px",
      "letterSpacing": "normal"
    },
    "caption-medium-2": {
      "fontFamily": "Plus Jakarta Sans",
      "fontSize": 11,
      "fontWeight": 500,
      "lineHeight": "16.5px",
      "letterSpacing": "normal"
    },
    "caption-medium-3": {
      "fontFamily": "Plus Jakarta Sans",
      "fontSize": 10,
      "fontWeight": 500,
      "lineHeight": "15px",
      "letterSpacing": "normal"
    },
    "caption-semibold": {
      "fontFamily": "Plus Jakarta Sans",
      "fontSize": 12,
      "fontWeight": 600,
      "lineHeight": "16px",
      "letterSpacing": "normal"
    },
    "caption-semibold-2": {
      "fontFamily": "Plus Jakarta Sans",
      "fontSize": 11,
      "fontWeight": 600,
      "lineHeight": "14px",
      "letterSpacing": "normal"
    },
    "caption-semibold-3": {
      "fontFamily": "Plus Jakarta Sans",
      "fontSize": 10,
      "fontWeight": 600,
      "lineHeight": "18px",
      "letterSpacing": "normal"
    },
    "caption-bold": {
      "fontFamily": "Plus Jakarta Sans",
      "fontSize": 12,
      "fontWeight": 700,
      "lineHeight": "16px",
      "letterSpacing": "normal"
    },
    "caption-bold-2": {
      "fontFamily": "Plus Jakarta Sans",
      "fontSize": 11,
      "fontWeight": 700,
      "lineHeight": "14px",
      "letterSpacing": "normal"
    },
    "caption-bold-3": {
      "fontFamily": "Plus Jakarta Sans",
      "fontSize": 10,
      "fontWeight": 700,
      "lineHeight": "15px",
      "letterSpacing": "0.5px"
    },
    "caption-bold-4": {
      "fontFamily": "Plus Jakarta Sans",
      "fontSize": 9,
      "fontWeight": 700,
      "lineHeight": "13.5px",
      "letterSpacing": "normal"
    }
  },
  "spacing": {
    "space-0": 0,
    "space-0.5": 0.5,
    "space-1": 1,
    "space-1.5": 1.5,
    "space-2": 2,
    "space-2.5": 2.5,
    "space-3": 3,
    "space-3.5": 3.5,
    "space-4": 4,
    "space-4.5": 4.5,
    "space-5": 5,
    "space-5.5": 5.5,
    "space-6": 6,
    "space-6.5": 6.5,
    "space-7": 7,
    "space-7.5": 7.5,
    "space-8": 8,
    "space-8.5": 8.5,
    "space-9": 9,
    "space-9.5": 9.5,
    "space-10": 10,
    "space-10.5": 10.5,
    "space-11": 11,
    "space-11.5": 11.5,
    "space-12": 12,
    "space-12.5": 12.5,
    "space-13": 13,
    "space-13.5": 13.5,
    "space-14": 14,
    "space-14.5": 14.5,
    "space-15": 15,
    "space-15.5": 15.5,
    "space-16": 16,
    "space-16.5": 16.5,
    "space-17": 17,
    "space-17.5": 17.5,
    "space-18": 18,
    "space-18.5": 18.5,
    "space-19": 19,
    "space-19.5": 19.5,
    "space-20": 20,
    "space-21": 21,
    "space-21.5": 21.5,
    "space-22": 22,
    "space-23": 23,
    "space-23.5": 23.5,
    "space-24": 24,
    "space-25": 25,
    "space-25.5": 25.5,
    "space-26": 26,
    "space-26.5": 26.5,
    "space-27": 27,
    "space-28": 28,
    "space-29": 29,
    "space-29.5": 29.5,
    "space-30": 30,
    "space-30.5": 30.5,
    "space-31.5": 31.5,
    "space-32": 32,
    "space-32.5": 32.5,
    "space-33": 33,
    "space-33.5": 33.5,
    "space-34": 34,
    "space-35": 35,
    "space-35.5": 35.5,
    "space-36": 36,
    "space-37": 37,
    "space-38": 38,
    "space-38.5": 38.5,
    "space-39": 39,
    "space-39.5": 39.5,
    "space-40": 40,
    "space-40.5": 40.5,
    "space-41": 41,
    "space-42": 42,
    "space-43": 43,
    "space-43.5": 43.5,
    "space-44": 44,
    "space-45.5": 45.5,
    "space-46": 46,
    "space-46.5": 46.5,
    "space-47.5": 47.5,
    "space-48": 48,
    "space-48.5": 48.5,
    "space-49": 49,
    "space-49.5": 49.5,
    "space-50": 50,
    "space-50.5": 50.5,
    "space-52.5": 52.5,
    "space-53": 53,
    "space-53.5": 53.5,
    "space-54": 54,
    "space-54.5": 54.5,
    "space-55": 55,
    "space-56": 56,
    "space-56.5": 56.5,
    "space-57": 57,
    "space-58": 58,
    "space-58.5": 58.5,
    "space-59": 59,
    "space-59.5": 59.5,
    "space-60": 60,
    "space-60.5": 60.5,
    "space-61": 61,
    "space-61.5": 61.5,
    "space-62": 62,
    "space-63": 63,
    "space-63.5": 63.5,
    "space-64": 64,
    "space-66": 66,
    "space-67": 67,
    "space-68": 68,
    "space-69": 69,
    "space-70": 70,
    "space-70.5": 70.5,
    "space-71": 71,
    "space-73.5": 73.5,
    "space-74": 74,
    "space-75": 75,
    "space-76": 76,
    "space-77": 77,
    "space-78": 78,
    "space-81": 81,
    "space-88": 88,
    "space-90.5": 90.5,
    "space-96": 96,
    "space-98": 98,
    "space-103": 103,
    "space-109": 109,
    "space-110": 110,
    "space-118": 118,
    "space-121": 121,
    "space-122": 122,
    "space-129": 129,
    "space-131": 131,
    "space-137": 137,
    "space-148": 148,
    "space-149": 149,
    "space-166": 166,
    "space-185": 185,
    "space-186": 186,
    "space-187": 187,
    "space-209": 209,
    "space-215": 215,
    "space-218": 218,
    "space-251": 251,
    "space-383": 383,
    "space-602": 602,
    "space-1158": 1158
  },
  "radius": {
    "radius-md": 8,
    "radius-sm": 6,
    "radius-sm-2": 4,
    "radius-sm-3": 2,
    "radius-lg-2": 12,
    "radius-full": 9999
  },
  "shadow": {
    "elevation-1": "0px 0px 0px 2px rgba(255, 255, 255, 1)",
    "elevation-2": "0px 1px 2px 0px rgba(0, 0, 0, 0.05)",
    "elevation-3": "0px 1px 2px -1px rgba(0, 0, 0, 0.1), 0px 1px 3px 0px rgba(0, 0, 0, 0.1)",
    "elevation-4": "0px 2px 4px -2px rgba(0, 0, 0, 0.1), 0px 4px 6px -1px rgba(0, 0, 0, 0.1)",
    "elevation-5": "0px 2px 4px 0px rgba(61, 35, 20, 0.04)",
    "elevation-6": "inset 0px 2px 4px 0px rgba(0, 0, 0, 0.05)",
    "elevation-7": "0px 4px 6px -4px rgba(0, 0, 0, 0.1), 0px 10px 15px -3px rgba(0, 0, 0, 0.1)",
    "elevation-8": "0px 6px 16px 0px rgba(61, 35, 20, 0.08)",
    "elevation-9": "0px 25px 50px -12px rgba(0, 0, 0, 0.25)"
  },
  "fonts": [
    "Plus Jakarta Sans",
    "Liberation Mono",
    "IPAGothic",
    "FreeSerif",
    "FreeSans"
  ]
}
```
