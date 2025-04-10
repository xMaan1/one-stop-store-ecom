from django.contrib import admin
from django.utils.html import format_html
from django.urls import path
from django.template.response import TemplateResponse
from django.db.models import Sum, Count
from django.utils import timezone
from datetime import timedelta
from .models import Banner,Category,Brand,Color,Size,Product,ProductAttribute,CartOrder,CartOrderItems,ProductReview,UserAddressBook,ProductGallery

# Custom Admin Site
class AlWaqarAdminSite(admin.AdminSite):
	site_header = "Al Waqar Administration"
	site_title = "Al Waqar Admin"
	index_title = "Welcome to Al Waqar Admin Panel"
	
	def get_urls(self):
		urls = super().get_urls()
		custom_urls = [
			path('dashboard/', self.admin_view(self.dashboard_view), name='dashboard'),
		]
		return custom_urls + urls
	
	def index(self, request, extra_context=None):
		# Redirect to our custom dashboard
		return self.dashboard_view(request)
	
	def dashboard_view(self, request):
		# Get date ranges
		today = timezone.now().date()
		week_ago = today - timedelta(days=7)
		month_ago = today - timedelta(days=30)
		
		# Get order stats
		total_orders = CartOrder.objects.count()
		orders_today = CartOrder.objects.filter(order_dt__date=today).count()
		orders_week = CartOrder.objects.filter(order_dt__date__gte=week_ago).count()
		
		# Revenue stats
		total_revenue = CartOrder.objects.filter(paid_status=True).aggregate(Sum('total_amt'))['total_amt__sum'] or 0
		revenue_today = CartOrder.objects.filter(paid_status=True, order_dt__date=today).aggregate(Sum('total_amt'))['total_amt__sum'] or 0
		revenue_week = CartOrder.objects.filter(paid_status=True, order_dt__date__gte=week_ago).aggregate(Sum('total_amt'))['total_amt__sum'] or 0
		
		# Product stats
		total_products = Product.objects.count()
		products_out_of_stock = ProductAttribute.objects.filter(stock=0).count()
		featured_products = Product.objects.filter(is_featured=True).count()
		
		# Customer stats
		total_reviews = ProductReview.objects.count()
		recent_reviews = ProductReview.objects.order_by('-created_at')[:5]
		
		# Recent orders
		recent_orders = CartOrder.objects.order_by('-order_dt')[:5]
		
		# Top selling products
		top_selling = CartOrderItems.objects.values('item').annotate(total_sales=Sum('qty')).order_by('-total_sales')[:5]
		top_products = []
		for item in top_selling:
			try:
				product = Product.objects.get(title=item['item'])
				image = ProductAttribute.objects.filter(product=product).first().image
				top_products.append({
					'title': item['item'],
					'sales': item['total_sales'],
					'image': image
				})
			except:
				pass
		
		# Order status distribution
		pending_orders = CartOrder.objects.filter(order_status='processing').count()
		shipped_orders = CartOrder.objects.filter(order_status='shipped').count()
		delivered_orders = CartOrder.objects.filter(order_status='delivered').count()
		
		context = {
			'title': 'Admin Dashboard',
			'total_orders': total_orders,
			'orders_today': orders_today,
			'orders_week': orders_week,
			'total_revenue': total_revenue,
			'revenue_today': revenue_today,
			'revenue_week': revenue_week,
			'total_products': total_products,
			'products_out_of_stock': products_out_of_stock,
			'featured_products': featured_products,
			'total_reviews': total_reviews,
			'recent_reviews': recent_reviews,
			'recent_orders': recent_orders,
			'top_products': top_products,
			'pending_orders': pending_orders,
			'shipped_orders': shipped_orders,
			'delivered_orders': delivered_orders,
			'has_permission': True,
			**self.each_context(request),
		}
		
		return TemplateResponse(request, 'admin/dashboard.html', context)

# Initialize custom admin site
admin_site = AlWaqarAdminSite(name='alwaqar_admin')

# Register models with enhanced admin interfaces
admin_site.register(Brand)
admin_site.register(Size)


class BannerAdmin(admin.ModelAdmin):
	list_display=('alt_text','image_tag')
	readonly_fields = ('image_tag',)
	
	class Media:
		css = {
			'all': ('admin/css/custom_admin.css',)
		}
admin_site.register(Banner,BannerAdmin)

class CategoryAdmin(admin.ModelAdmin):
	list_display=('title','image_tag')
	readonly_fields = ('image_tag',)
	search_fields = ('title',)
	
	class Media:
		css = {
			'all': ('admin/css/custom_admin.css',)
		}
admin_site.register(Category,CategoryAdmin)

class ColorAdmin(admin.ModelAdmin):
	list_display=('title','color_bg')
	search_fields = ('title',)
	
	class Media:
		css = {
			'all': ('admin/css/custom_admin.css',)
		}
admin_site.register(Color,ColorAdmin)

# Product Gallery Inline
class ProductGalleryInline(admin.TabularInline):
	model = ProductGallery
	extra = 1
	readonly_fields = ('image_tag',)

class ProductAdmin(admin.ModelAdmin):
	list_display=('id','title','category','brand','status','is_featured', 'inventory_status')
	list_editable=('status','is_featured')
	search_fields = ('title', 'detail', 'specs')
	list_filter = ('category', 'brand', 'status', 'is_featured')
	prepopulated_fields = {'slug': ('title',)}
	inlines = [ProductGalleryInline]
	fieldsets = (
		('Basic Information', {
			'fields': ('title', 'slug', 'category', 'brand')
		}),
		('Product Details', {
			'fields': ('detail', 'specs', 'status', 'is_featured', 'sku')
		}),
	)
	
	def inventory_status(self, obj):
		attrs = ProductAttribute.objects.filter(product=obj)
		total_stock = sum(attr.stock for attr in attrs)
		if total_stock == 0:
			return format_html('<span style="color:red; font-weight:bold;">Out of Stock</span>')
		elif total_stock < 5:
			return format_html('<span style="color:orange; font-weight:bold;">Low Stock ({})</span>', total_stock)
		else:
			return format_html('<span style="color:green; font-weight:bold;">In Stock ({})</span>', total_stock)
	
	inventory_status.short_description = 'Inventory'
	
	class Media:
		css = {
			'all': ('admin/css/custom_admin.css',)
		}
admin_site.register(Product,ProductAdmin)

# Product Gallery Admin
class ProductGalleryAdmin(admin.ModelAdmin):
	list_display = ('id', 'product', 'image_tag', 'is_primary', 'created_at')
	list_editable = ('is_primary',)
	list_filter = ('product', 'is_primary')
	
	class Media:
		css = {
			'all': ('admin/css/custom_admin.css',)
		}
admin_site.register(ProductGallery, ProductGalleryAdmin)

# Product Attribute
class ProductAttributeAdmin(admin.ModelAdmin):
	list_display=('id','image_tag','product','price','color','size','stock','stock_status','is_default')
	list_filter = ('product', 'color', 'size', 'is_default')
	list_editable = ('price', 'stock', 'is_default')
	search_fields = ('product__title',)
	
	def stock_status(self, obj):
		if obj.stock == 0:
			return format_html('<span style="color:red; font-weight:bold;">Out of Stock</span>')
		elif obj.stock < 5:
			return format_html('<span style="color:orange; font-weight:bold;">Low Stock ({})</span>', obj.stock)
		else:
			return format_html('<span style="color:green; font-weight:bold;">In Stock ({})</span>', obj.stock)
	
	stock_status.short_description = 'Stock Status'
	
	class Media:
		css = {
			'all': ('admin/css/custom_admin.css',)
		}
admin_site.register(ProductAttribute,ProductAttributeAdmin)

# Order
class CartOrderAdmin(admin.ModelAdmin):
	list_display=('user','total_amt','paid_status','order_dt','order_status', 'payment_method')
	list_editable=('paid_status','order_status')
	list_filter = ('paid_status', 'order_status', 'order_dt')
	search_fields = ('user__username',)
	
	def payment_method(self, obj):
		if obj.paid_status:
			return format_html('<span style="color:green">Cash On Delivery</span>')
		return format_html('<span style="color:orange">Pending</span>')
	
	class Media:
		css = {
			'all': ('admin/css/custom_admin.css',)
		}
admin_site.register(CartOrder,CartOrderAdmin)

class CartOrderItemsAdmin(admin.ModelAdmin):
	list_display=('invoice_no','item','image_tag','qty','price','total')
	search_fields = ('invoice_no', 'item')
	
	class Media:
		css = {
			'all': ('admin/css/custom_admin.css',)
		}
admin_site.register(CartOrderItems,CartOrderItemsAdmin)


class ProductReviewAdmin(admin.ModelAdmin):
	list_display=('user','product','review_text','get_review_rating','created_at')
	list_filter = ('review_rating', 'created_at')
	search_fields = ('user__username', 'product__title', 'review_text')
	
	class Media:
		css = {
			'all': ('admin/css/custom_admin.css',)
		}
admin_site.register(ProductReview,ProductReviewAdmin)


# Address Book Admin
class UserAddressBookAdmin(admin.ModelAdmin):
	list_display=('user','address','mobile','status')
	list_editable = ('status',)
	list_filter = ('status',)
	search_fields = ('user__username', 'address', 'mobile')
	
	class Media:
		css = {
			'all': ('admin/css/custom_admin.css',)
		}
admin_site.register(UserAddressBook,UserAddressBookAdmin)

# Replace default admin site with our custom one
admin.site = admin_site