# SEOPress WPGraphQL

Expose tous les champs SEO de **SEOPress** dans l'API **WPGraphQL**.

## Prérequis

| Plugin | Version minimale |
|--------|-----------------|
| WordPress | 5.8+ |
| WPGraphQL | 1.x+ |
| SEOPress | 5.x+ |
| PHP | 7.4+ |

## Installation

1. Copier le dossier `seopress-graphql/` dans `wp-content/plugins/`.
2. Activer le plugin via **Extensions → Extensions installées**.
3. S'assurer que SEOPress et WPGraphQL sont activés.

## Champs exposés

Le plugin ajoute un champ `seo` sur chaque type de post et chaque terme de taxonomie enregistré dans WPGraphQL.

### Type racine `SEOPressSEO`

| Champ | Type | Description |
|-------|------|-------------|
| `title` | `String` | Titre SEO (variables remplacées) |
| `metaDescription` | `String` | Méta description |
| `canonicalUrl` | `String` | URL canonique |
| `targetKeywords` | `[String]` | Mots-clés cibles |
| `robots` | `SEOPressRobots` | Directives robots |
| `openGraph` | `SEOPressOpenGraph` | Données Open Graph / Facebook |
| `twitterCard` | `SEOPressTwitterCard` | Twitter Card |
| `schema` | `SEOPressSchema` | JSON-LD / données structurées |
| `breadcrumbs` | `[SEOPressBreadcrumbItem]` | Fil d'Ariane |
| `redirect` | `SEOPressRedirect` | Redirection (SEOPress PRO) |
| `sitemapExclude` | `Boolean` | Exclu du sitemap ? |
| `sitemapPriority` | `String` | Priorité sitemap |
| `sitemapFrequency` | `String` | Fréquence sitemap |

### `SEOPressRobots`

| Champ | Type |
|-------|------|
| `noIndex` | `Boolean` |
| `noFollow` | `Boolean` |
| `noOdp` | `Boolean` |
| `noImageIndex` | `Boolean` |
| `noArchive` | `Boolean` |
| `noSnippet` | `Boolean` |
| `primary` | `String` |

### `SEOPressOpenGraph`

| Champ | Type |
|-------|------|
| `title` | `String` |
| `description` | `String` |
| `image` | `String` |
| `imageWidth` | `String` |
| `imageHeight` | `String` |
| `type` | `String` |
| `url` | `String` |
| `siteName` | `String` |
| `videoUrl` | `String` |
| `locale` | `String` |
| `facebookApp` | `String` |

### `SEOPressTwitterCard`

| Champ | Type |
|-------|------|
| `title` | `String` |
| `description` | `String` |
| `image` | `String` |
| `cardType` | `String` |
| `creator` | `String` |
| `site` | `String` |

### `SEOPressSchema`

| Champ | Type |
|-------|------|
| `type` | `String` |
| `pageType` | `String` |
| `articleType` | `String` |
| `jsonLd` | `String` |

### `SEOPressBreadcrumbItem`

| Champ | Type |
|-------|------|
| `text` | `String` |
| `url` | `String` |

### `SEOPressRedirect`

| Champ | Type |
|-------|------|
| `enabled` | `Boolean` |
| `type` | `String` |
| `url` | `String` |

---

## Exemples de requêtes GraphQL

### Post — titre et méta

```graphql
query PostSEO {
  post(id: "1", idType: DATABASE_ID) {
    title
    seo {
      title
      metaDescription
      canonicalUrl
    }
  }
}
```

### Post — données complètes

```graphql
query FullPostSEO {
  post(id: "1", idType: DATABASE_ID) {
    title
    seo {
      title
      metaDescription
      canonicalUrl
      targetKeywords
      robots {
        noIndex
        noFollow
        noArchive
      }
      openGraph {
        title
        description
        image
        type
      }
      twitterCard {
        title
        description
        image
        cardType
      }
      schema {
        pageType
        articleType
      }
      breadcrumbs {
        text
        url
      }
      sitemapExclude
      sitemapPriority
    }
  }
}
```

### Liste d'articles avec SEO

```graphql
query PostsSEO {
  posts(first: 10) {
    nodes {
      id
      title
      slug
      seo {
        title
        metaDescription
        robots {
          noIndex
        }
        openGraph {
          image
        }
      }
    }
  }
}
```

### Page avec fil d'Ariane

```graphql
query PageBreadcrumbs {
  page(id: "42", idType: DATABASE_ID) {
    title
    seo {
      breadcrumbs {
        text
        url
      }
    }
  }
}
```

### Terme de taxonomie

```graphql
query CategorySEO {
  category(id: "5", idType: DATABASE_ID) {
    name
    seo {
      title
      metaDescription
      robots {
        noIndex
      }
      openGraph {
        title
        image
      }
    }
  }
}
```

### Type personnalisé (ex: `product` WooCommerce)

```graphql
query ProductSEO {
  product(id: "99", idType: DATABASE_ID) {
    name
    seo {
      title
      metaDescription
      schema {
        type
        jsonLd
      }
    }
  }
}
```

---

## Notes techniques

- Le plugin lit les méta SEOPress directement depuis la base de données (`get_post_meta` / `get_term_meta`).
- Si le service `TitleService` de SEOPress 5.x est disponible, le titre est **rendu** (variables dynamiques remplacées).
- Les fils d'Ariane utilisent le service breadcrumb SEOPress 5.x si disponible, sinon un fallback simple (accueil → ancêtres → post actuel).
- Les champs **redirect** nécessitent **SEOPress PRO**.
- Le plugin est compatible avec les Custom Post Types et taxonomies personnalisées à condition qu'ils aient `show_in_graphql => true`.

## Compatibilité

- ✅ SEOPress (free) 5.x
- ✅ SEOPress PRO 5.x (champs redirect)
- ✅ WPGraphQL 1.x
- ✅ WPGraphQL for ACF (coexistence)
- ✅ Headless WordPress (Next.js, Gatsby, Nuxt, etc.)

## Licence

GPL-2.0-or-later
