<!-- 显示虚拟行项目 (定制费用) -->
<div v-if="item.children && item.children.length > 0" class="customization-children-info mt-1">
  <div v-for="child in item.children" :key="child.id" class="text-secondary small">
    @{{ child.sku_code }} x @{{ child.quantity }}
    <span class="ms-2">+ @{{ formatCurrency(child.price * child.quantity) }}</span>
  </div>
</div>
