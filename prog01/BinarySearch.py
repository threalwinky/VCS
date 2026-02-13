def BinarySearch(arr, target, l, r):
   while l <= r:
       m = l + (r - l) // 2
      
       if arr[m] == target:
           return f"Tìm thấy tại vị trí {m}"
       elif arr[m] < target:
           l = m + 1
       else:
           r = m - 1
          
   return "Không tìm thấy"

arr = [1, 3, 6, 100, 102, 449, 600, 911, 1230, 12312, 12313, 55545, 1232345]
x1 = 911
x2 = 912
print(BinarySearch(arr, x1, 0, len(arr) - 1))
print(BinarySearch(arr, x2, 0, len(arr) - 1))